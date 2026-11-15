<?php

namespace App\Http\Controllers\Public;

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use App\Enums\AccompanimentToolPaymentStatus;
use App\Enums\AccompanimentToolRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolAccessRequest;
use App\Models\AccompanimentToolPayment;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccompanimentToolPublicController extends Controller
{
    public function index(): View
    {
        return view('pages.outils');
    }

    public function access(Request $request, string $slug): StreamedResponse|RedirectResponse
    {
        $tool = AccompanimentTool::where('slug', $slug)->firstOrFail();
        $decision = $tool->evaluateAccessFor($request->user());

        if (! $decision->canAccess) {
            if ($decision->code === 'AUTHENTICATION_REQUIRED') {
                return redirect()->route('public.rejoindre')
                    ->with('tool_notice', $decision->message);
            }

            return redirect()->route('public.outils')
                ->with('tool_error', $decision->message);
        }

        if ($tool->format === AccompanimentToolFormat::DRIVE_LINK) {
            if (blank($tool->drive_url)) {
                abort(404, 'Lien Google Drive introuvable pour cet outil.');
            }

            return redirect()->away($tool->drive_url);
        }

        if (blank($tool->file_path) || ! Storage::disk('local')->exists($tool->file_path)) {
            abort(404, 'Le document demandé est introuvable sur le serveur.');
        }

        $downloadName = $tool->original_filename ?: (Str::slug($tool->title).'.'.pathinfo($tool->file_path, PATHINFO_EXTENSION));

        return Storage::disk('local')->download($tool->file_path, $downloadName);
    }

    public function requestAccess(Request $request, string $slug): RedirectResponse
    {
        $buyer = $request->user();
        if (! $buyer) {
            return redirect()->route('public.rejoindre')
                ->with('tool_notice', 'Veuillez vous connecter pour demander l\'accès à cet outil.');
        }

        $tool = AccompanimentTool::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if ($tool->access_level !== AccompanimentToolAccessLevel::ON_REQUEST) {
            return redirect()->route('public.outils')
                ->with('tool_error', 'Cet outil ne nécessite pas de demande d\'autorisation.');
        }

        $validated = $request->validate([
            'request_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $existingRequest = AccompanimentToolAccessRequest::where('accompaniment_tool_id', $tool->id)
            ->where('user_id', $buyer->id)
            ->first();

        if ($existingRequest && $existingRequest->status === AccompanimentToolRequestStatus::APPROVED) {
            return redirect()->route('public.outils')
                ->with('tool_success', 'Votre accès à cet outil est déjà approuvé.');
        }

        AccompanimentToolAccessRequest::updateOrCreate(
            [
                'accompaniment_tool_id' => $tool->id,
                'user_id' => $buyer->id,
            ],
            [
                'sae_id' => $tool->sae_id,
                'status' => AccompanimentToolRequestStatus::PENDING,
                'request_message' => $validated['request_message'] ?? null,
                'decision_note' => null,
                'reviewed_by_id' => null,
                'reviewed_at' => null,
            ],
        );

        return redirect()->route('public.outils')
            ->with('tool_success', "Votre demande d'accès pour « {$tool->title} » a bien été envoyée à la structure.");
    }

    public function initiatePayment(Request $request, string $slug): RedirectResponse
    {
        $buyer = $request->user();
        if (! $buyer) {
            return redirect()->route('public.rejoindre')
                ->with('tool_notice', 'Veuillez vous connecter pour acheter cet outil.');
        }

        $tool = AccompanimentTool::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if ($tool->access_level !== AccompanimentToolAccessLevel::PAID || (int) $tool->price <= 0) {
            return redirect()->route('public.outils')
                ->with('tool_error', 'Cet outil n\'est pas configuré comme outil payant.');
        }

        $alreadyPaid = AccompanimentToolPayment::where('accompaniment_tool_id', $tool->id)
            ->where('user_id', $buyer->id)
            ->where('status', AccompanimentToolPaymentStatus::PAID->value)
            ->exists();

        if ($alreadyPaid) {
            return redirect()->route('public.outils.access', ['slug' => $tool->slug]);
        }

        $gateway = PaymentGatewayResolver::resolve();
        $reference = 'TOOL-'.Str::upper(Str::random(12));

        $payment = AccompanimentToolPayment::create([
            'accompaniment_tool_id' => $tool->id,
            'sae_id' => $tool->sae_id,
            'user_id' => $buyer->id,
            'reference' => $reference,
            'gateway' => $gateway->providerName(),
            'amount' => (int) $tool->price,
            'currency' => $tool->currency ?: 'XOF',
            'status' => AccompanimentToolPaymentStatus::PENDING,
        ]);

        $callbackUrl = route('public.outils.payment.callback', ['reference' => $payment->reference]);
        $checkoutSession = $gateway->initiateCheckout($payment, $tool, $buyer, $callbackUrl);

        $payment->update([
            'external_reference' => $checkoutSession->externalReference,
        ]);

        return redirect()->away($checkoutSession->checkoutUrl);
    }

    public function paymentCallback(Request $request, string $reference): RedirectResponse
    {
        $payment = AccompanimentToolPayment::with('tool')
            ->where('reference', $reference)
            ->firstOrFail();

        if ($payment->status === AccompanimentToolPaymentStatus::PAID) {
            return redirect()->route('public.outils')
                ->with('tool_success', "Accès déjà débloqué pour « {$payment->tool?->title} ».");
        }

        $gateway = PaymentGatewayResolver::resolve();
        $verification = $gateway->verifyCallback($payment, $request->all());

        if (! $verification->isPaid) {
            $payment->update(['status' => AccompanimentToolPaymentStatus::FAILED]);

            return redirect()->route('public.outils')
                ->with('tool_error', $verification->errorMessage ?: 'Le paiement n\'a pas pu être confirmé.');
        }

        $payment->update([
            'status' => AccompanimentToolPaymentStatus::PAID,
            'external_reference' => $verification->externalReference ?: $payment->external_reference,
            'paid_at' => now(),
        ]);

        return redirect()->route('public.outils')
            ->with('tool_success', "Paiement confirmé ! Vous pouvez maintenant accéder à « {$payment->tool?->title} ».");
    }
}
