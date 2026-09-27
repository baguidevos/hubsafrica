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
    | Ces valeurs sont injectées dynamiquement dans les variables CSS (--ha-*)
    | et permettent une personnalisation sans avoir à recompiler les assets.
    */
    'theme' => [
        // Vert Émeraude Institutionnel (Croissance, Impact, Durabilité)
        'primary' => env('HUBAFRICA_COLOR_PRIMARY', '#064e3b'),
        'primary_hover' => env('HUBAFRICA_COLOR_PRIMARY_HOVER', '#04382a'),
        'primary_light' => env('HUBAFRICA_COLOR_PRIMARY_LIGHT', '#059669'),
        
        // Bleu Nuit / Ardoise Impériale (Autorité, Solidité institutionnelle)
        'secondary' => env('HUBAFRICA_COLOR_SECONDARY', '#0f172a'),
        'secondary_light' => env('HUBAFRICA_COLOR_SECONDARY_LIGHT', '#1e293b'),
        
        // Or / Ambre Panafricain (Excellence, Rayonnement, Innovation)
        'accent' => env('HUBAFRICA_COLOR_ACCENT', '#d97706'),
        'accent_light' => env('HUBAFRICA_COLOR_ACCENT_LIGHT', '#f59e0b'),
        
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
        'keywords' => 'entrepreneuriat, afrique, incubation, accélération, startups, SAE, mentorat, financement, CUBE',
    ],
];
