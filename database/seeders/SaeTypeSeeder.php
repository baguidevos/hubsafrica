<?php

namespace Database\Seeders;

use App\Models\SaeType;
use Illuminate\Database\Seeder;

class SaeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Incubateur d\'entreprises',
                'slug' => 'incubateur',
                'description' => 'Structure d\'appui accompagnant les porteurs de projet de l\'idée au prototypage et à la validation du modèle d\'affaires initial.',
                'color' => 'primary',
                'icon' => 'heroicon-o-light-bulb',
                'is_active' => true,
            ],
            [
                'name' => 'Accélérateur de startups',
                'slug' => 'accelerateur',
                'description' => 'Programme intensif axé sur le passage à l\'échelle (scale-up), la traction commerciale et la levée de fonds.',
                'color' => 'success',
                'icon' => 'heroicon-o-rocket-launch',
                'is_active' => true,
            ],
            [
                'name' => 'Pépinière d\'entreprises',
                'slug' => 'pepiniere',
                'description' => 'Structure d\'accueil offrant locaux, services partagés et suivi managérial aux entreprises nouvellement créées.',
                'color' => 'warning',
                'icon' => 'heroicon-o-building-storefront',
                'is_active' => true,
            ],
            [
                'name' => 'Couveuse d\'entreprises',
                'slug' => 'couveuse',
                'description' => 'Dispositif juridique et opérationnel permettant de tester la viabilité d\'une activité en situation réelle avant immatriculation.',
                'color' => 'info',
                'icon' => 'heroicon-o-shield-check',
                'is_active' => true,
            ],
            [
                'name' => 'Espace de coworking',
                'slug' => 'espace-coworking',
                'description' => 'Espace de travail partagé stimulant les synergies professionnelles, le travail collaboratif et l\'animation de communauté.',
                'color' => 'indigo',
                'icon' => 'heroicon-o-users',
                'is_active' => true,
            ],
            [
                'name' => 'FabLab / MakerSpace',
                'slug' => 'fablab-makerspace',
                'description' => 'Atelier de fabrication numérique et de prototypage rapide équipé de machines partagées (impression 3D, IoT, découpeuse laser).',
                'color' => 'amber',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'is_active' => true,
            ],
            [
                'name' => 'Tiers-lieu d\'innovation',
                'slug' => 'tiers-lieu',
                'description' => 'Espace hybride favorisant la créativité partagée, l\'open innovation et l\'engagement des acteurs territoriaux.',
                'color' => 'purple',
                'icon' => 'heroicon-o-sparkles',
                'is_active' => true,
            ],
            [
                'name' => 'Technopôle / Hub technologique',
                'slug' => 'technopole-hub',
                'description' => 'Pôle territorial regroupant laboratoires de recherche, universités et entreprises technologiques innovantes.',
                'color' => 'teal',
                'icon' => 'heroicon-o-cpu-chip',
                'is_active' => true,
            ],
            [
                'name' => 'Hôtel d\'entreprises',
                'slug' => 'hotel-entreprises',
                'description' => 'Locaux professionnels et bureaux modulables dédiés aux entreprises en phase de consolidation post-création.',
                'color' => 'gray',
                'icon' => 'heroicon-o-building-office',
                'is_active' => true,
            ],
            [
                'name' => 'Pôle de compétitivité / Cluster',
                'slug' => 'pole-competitivite-cluster',
                'description' => 'Réseau sectoriel réunissant startups, PME, grands groupes et centres académiques autour de filières stratégiques.',
                'color' => 'rose',
                'icon' => 'heroicon-o-globe-alt',
                'is_active' => true,
            ],
        ];

        foreach ($types as $typeData) {
            SaeType::firstOrCreate(
                ['slug' => $typeData['slug']],
                $typeData
            );
        }
    }
}
