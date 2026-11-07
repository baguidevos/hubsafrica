<?php

namespace Database\Seeders;

use App\Models\Sae;
use App\Services\OpportunityImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class OpportunityCubeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(OpportunityImportService $service): void
    {
        $jsonPath = database_path('data/opportunities_cube.json');
        if (! file_exists($jsonPath)) {
            Log::warning("Fichier introuvable pour OpportunityCubeSeeder : {$jsonPath}");

            return;
        }

        $sae = Sae::where('name', 'like', '%CUBE%')
            ->orWhere('slug', 'like', '%cube%')
            ->first() ?: Sae::first();

        if (! $sae) {
            Log::warning('Aucune structure SAE trouvée pour OpportunityCubeSeeder.');

            return;
        }

        $result = $service->importFromFile($jsonPath, $sae);
        $this->command->info("OpportunityCubeSeeder: {$result['imported_count']} opportunités importées pour {$sae->name}.");
    }
}
