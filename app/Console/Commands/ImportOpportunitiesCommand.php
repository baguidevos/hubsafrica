<?php

namespace App\Console\Commands;

use App\Models\Sae;
use App\Services\OpportunityImportService;
use Illuminate\Console\Command;

class ImportOpportunitiesCommand extends Command
{
    protected $signature = 'opportunities:import 
                            {file? : Chemin vers le fichier JSON, CSV ou XLSX (par défaut: database/data/opportunities_cube.json)} 
                            {--sae= : ID ou slug de la structure SAE réceptrice}';

    protected $description = 'Importe un lot d\'opportunités et appels à projets depuis un fichier JSON, CSV ou Excel';

    public function handle(OpportunityImportService $service): int
    {
        $filePath = $this->argument('file') ?: base_path('database/data/opportunities_cube.json');

        if (! file_exists($filePath)) {
            $this->error("Fichier introuvable : {$filePath}");

            return self::FAILURE;
        }

        // Sélection ou détection de la structure SAE
        $saeIdentifier = $this->option('sae');
        $sae = null;

        if ($saeIdentifier) {
            $sae = is_numeric($saeIdentifier)
                ? Sae::find((int) $saeIdentifier)
                : Sae::where('slug', $saeIdentifier)->orWhere('name', 'like', "%{$saeIdentifier}%")->first();
        } else {
            // Tenter de trouver CUBE en priorité, sinon la première SAE disponible
            $sae = Sae::where('name', 'like', '%CUBE%')
                ->orWhere('slug', 'like', '%cube%')
                ->first() ?: Sae::first();
        }

        if (! $sae) {
            $this->error('Aucune structure SAE trouvée dans la base de données pour associer ces opportunités.');

            return self::FAILURE;
        }

        $this->info("Importation en cours vers la structure : [{$sae->id}] {$sae->name}...");
        $this->line("Source : {$filePath}");

        try {
            $result = $service->importFromFile($filePath, $sae);

            $this->info(" Succès : {$result['imported_count']} opportunité(s) importée(s) !");

            if ($result['skipped_count'] > 0) {
                $this->warn(" Ignoré(s) : {$result['skipped_count']}");
                foreach ($result['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Échec de l\'importation : '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
