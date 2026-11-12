<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\InteractsWithSae;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('sae_profile')]
#[Title('Profil institutionnel de la structure (SAE)')]
#[Description('Fournit les métadonnées officielles, la présentation, les domaines d\'intervention et les coordonnées de l\'incubateur.')]
#[Uri('hubafrica://sae/profile')]
#[MimeType('application/json')]
class SaeProfileResource extends Resource
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        return Response::text(json_encode([
            'id' => $sae->id,
            'name' => $sae->name,
            'code' => $sae->code,
            'description' => $sae->description,
            'country' => $sae->country,
            'city' => $sae->city,
            'address' => $sae->address,
            'email' => $sae->email,
            'phone' => $sae->phone,
            'website' => $sae->website,
            'admission_threshold' => $sae->getAdmissionThreshold(),
            'waitlist_threshold' => $sae->getWaitlistThreshold(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
