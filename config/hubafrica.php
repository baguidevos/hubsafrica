<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identité Institutionnelle de la Plateforme
    |--------------------------------------------------------------------------
    */
    'name' => env('HUBS_AFRICA_APP_NAME', 'Hubs Africa'),
    'version' => env('HUBS_AFRICA_VERSION', '2.12.0'),
    'tagline' => env('HUBS_AFRICA_TAGLINE', 'Plateforme Panafricaine d\'Accompagnement et de Suivi de l\'Innovation'),
    'description' => env('HUBS_AFRICA_DESCRIPTION', 'L\'écosystème institutionnel qui unifie les structures d\'accompagnement (SAE), les mentors experts et les entrepreneurs à fort impact en Afrique.'),

    /*
    |--------------------------------------------------------------------------
    | Thème & Couleurs Institutionnelles (Modifiables via .env ou ici)
    |--------------------------------------------------------------------------
    | Couleurs officielles issues de la Version 1 de Hubs-Africa :
    | - Primaire : Or / Ambre (#F8B81D)
    | - Secondaire : Bleu Indigo profond (#2F3284)
    | - Accent : Orange / Jaune chaud (#F9B104)
    */
    'theme' => [
        // Or / Jaune Hubs-Africa V1 (Actions principales, highlights, badges)
        'primary' => env('HUBS_AFRICA_COLOR_PRIMARY', '#F8B81D'),
        'primary_hover' => env('HUBS_AFRICA_COLOR_PRIMARY_HOVER', '#e5a510'),
        'primary_light' => env('HUBS_AFRICA_COLOR_PRIMARY_LIGHT', '#fef3c7'),

        // Bleu Indigo profond Hubs-Africa V1 (Titres, navigation, contraste)
        'secondary' => env('HUBS_AFRICA_COLOR_SECONDARY', '#2F3284'),
        'secondary_light' => env('HUBS_AFRICA_COLOR_SECONDARY_LIGHT', '#4347a8'),

        // Accent Orange / Jaune chaud Hubs-Africa V1
        'accent' => env('HUBS_AFRICA_COLOR_ACCENT', '#F9B104'),
        'accent_light' => env('HUBS_AFRICA_COLOR_ACCENT_LIGHT', '#fbbf24'),

        // Nuances de fond et de surfaces
        'surface' => env('HUBS_AFRICA_COLOR_SURFACE', '#f8fafc'),
        'card_bg' => env('HUBS_AFRICA_COLOR_CARD_BG', '#ffffff'),
        'border' => env('HUBS_AFRICA_COLOR_BORDER', '#e2e8f0'),

        // Typographie
        'text_dark' => env('HUBS_AFRICA_COLOR_TEXT_DARK', '#0f172a'),
        'text_muted' => env('HUBS_AFRICA_COLOR_TEXT_MUTED', '#64748b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Coordonnées Officielles & Support
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => env('HUBS_AFRICA_CONTACT_EMAIL', env('HUBAFRICA_CONTACT_EMAIL', 'teamhubafrica@gmail.com')),
        'phone' => env('HUBS_AFRICA_CONTACT_PHONE', env('HUBAFRICA_CONTACT_PHONE', '+228 70 76 76 94')),
        'address' => env('HUBS_AFRICA_CONTACT_ADDRESS', env('HUBAFRICA_CONTACT_ADDRESS', 'TOGO, Lomé.')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Métadonnées SEO par défaut
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'title' => 'Hubs Africa — Plateforme Panafricaine d\'Accompagnement & Suivi de l\'Innovation',
        'description' => 'Découvrez les programmes d\'incubation en Afrique, candidatez aux appels à projets et bénéficiez de l\'accompagnement de mentors accrédités.',
        'og_image' => '/images/hubs-africa-og.jpg',
        'keywords' => 'entrepreneuriat, afrique, incubation, accélération, startups, SAE, mentorat, financement, accompagnement',
    ],
];
