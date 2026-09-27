<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identité Institutionnelle de la Plateforme
    |--------------------------------------------------------------------------
    */
    'name' => env('HUBAFRICA_APP_NAME', 'Hub Africa'),
    'tagline' => env('HUBAFRICA_TAGLINE', 'Plateforme Panafricaine d\'Accompagnement et de Suivi de l\'Innovation'),
    'description' => env('HUBAFRICA_DESCRIPTION', 'L\'écosystème institutionnel qui unifie les structures d\'accompagnement (SAE), les mentors experts et les entrepreneurs à fort impact en Afrique.'),

    /*
    |--------------------------------------------------------------------------
    | Thème & Couleurs Institutionnelles (Modifiables via .env ou ici)
    |--------------------------------------------------------------------------
    | Couleurs officielles issues de la Version 1 de Hub-Africa :
    | - Primaire : Or / Ambre (#F8B81D)
    | - Secondaire : Bleu Indigo profond (#2F3284)
    | - Accent : Orange / Jaune chaud (#F9B104)
    */
    'theme' => [
        // Or / Jaune Hub-Africa V1 (Actions principales, highlights, badges)
        'primary' => env('HUBAFRICA_COLOR_PRIMARY', '#F8B81D'),
        'primary_hover' => env('HUBAFRICA_COLOR_PRIMARY_HOVER', '#e5a510'),
        'primary_light' => env('HUBAFRICA_COLOR_PRIMARY_LIGHT', '#fef3c7'),
        
        // Bleu Indigo profond Hub-Africa V1 (Titres, navigation, contraste)
        'secondary' => env('HUBAFRICA_COLOR_SECONDARY', '#2F3284'),
        'secondary_light' => env('HUBAFRICA_COLOR_SECONDARY_LIGHT', '#4347a8'),
        
        // Accent Orange / Jaune chaud Hub-Africa V1
        'accent' => env('HUBAFRICA_COLOR_ACCENT', '#F9B104'),
        'accent_light' => env('HUBAFRICA_COLOR_ACCENT_LIGHT', '#fbbf24'),
        
        // Nuances de fond et de surfaces
        'surface' => env('HUBAFRICA_COLOR_SURFACE', '#f8fafc'),
        'card_bg' => env('HUBAFRICA_COLOR_CARD_BG', '#ffffff'),
        'border' => env('HUBAFRICA_COLOR_BORDER', '#e2e8f0'),
        
        // Typographie
        'text_dark' => env('HUBAFRICA_COLOR_TEXT_DARK', '#0f172a'),
        'text_muted' => env('HUBAFRICA_COLOR_TEXT_MUTED', '#64748b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Coordonnées Officielles & Support
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => env('HUBAFRICA_CONTACT_EMAIL', 'contact@hubafrica.org'),
        'phone' => env('HUBAFRICA_CONTACT_PHONE', '+221 33 800 00 00'),
        'address' => env('HUBAFRICA_CONTACT_ADDRESS', 'Secrétariat Régional Panafricain — Dakar, Sénégal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Métadonnées SEO par défaut
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'title' => 'Hub Africa — Plateforme Panafricaine d\'Accompagnement & Suivi de l\'Innovation',
        'description' => 'Découvrez les programmes d\'incubation en Afrique, candidatez aux appels à projets et bénéficiez de l\'accompagnement de mentors accrédités.',
        'og_image' => '/images/hubafrica-og.jpg',
        'keywords' => 'entrepreneuriat, afrique, incubation, accélération, startups, SAE, mentorat, financement, accompagnement',
    ],
];
