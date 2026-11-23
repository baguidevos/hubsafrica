<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseDumpService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function __construct(private readonly DatabaseDumpService $dumpService) {}

    /**
     * Télécharge une sauvegarde existante depuis le stockage privé (Super Admin uniquement).
     */
    public function download(Request $request, string $filename): BinaryFileResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Accès réservé aux Super Administrateurs Hubs Africa.');
        }

        try {
            $absolutePath = $this->dumpService->resolveBackupPath($filename);
        } catch (InvalidArgumentException) {
            abort(400, 'Nom de fichier de sauvegarde invalide.');
        } catch (RuntimeException) {
            abort(404, 'Sauvegarde introuvable.');
        }

        $contentType = str_ends_with($filename, '.gz') ? 'application/gzip' : 'application/sql';

        return response()->download($absolutePath, $filename, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
