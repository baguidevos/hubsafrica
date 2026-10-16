@php
    $currentPath = request()->path();
    $currentPanel = 'general';
    $panelTitle = 'Hub Africa';
    $panelBadge = 'Espace Sécurisé';
    $badgeBg = 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    $accentColor = '#F8B81D';

    if (str_contains($currentPath, 'admin')) {
        $currentPanel = 'admin';
        $panelTitle = 'Administration Centrale';
        $panelBadge = 'Supervision Panafricaine';
        $badgeBg = 'bg-slate-900 text-slate-100 border-slate-700';
        $accentColor = '#94a3b8';
    } elseif (str_contains($currentPath, 'sae')) {
        $currentPanel = 'sae';
        $panelTitle = 'Structures d\'Accompagnement (SAE)';
        $panelBadge = 'Incubateurs & Accélérateurs';
        $badgeBg = 'bg-teal-500/15 text-teal-700 dark:text-teal-300 border-teal-500/30';
        $accentColor = '#14b8a6';
    } elseif (str_contains($currentPath, 'coach')) {
        $currentPanel = 'coach';
        $panelTitle = 'Espace Coach & Mentor';
        $panelBadge = 'Experts Métiers & Conseillers';
        $badgeBg = 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border-indigo-500/30';
        $accentColor = '#6366f1';
    } elseif (str_contains($currentPath, 'portal') || str_contains($currentPath, 'entrepreneur')) {
        $currentPanel = 'entrepreneur';
        $panelTitle = 'Espace Porteur de Projet';
        $panelBadge = 'Startups & Entreprises Accompagnées';
        $badgeBg = 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30';
        $accentColor = '#10b981';
    }

    $isRegister = str_contains($currentPath, 'register');
    $isReset = str_contains($currentPath, 'password-reset');
@endphp

<div class="mb-5 text-center space-y-2">
    {{-- Badge de l'espace actif --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeBg }}">
        <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F8B81D] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F8B81D]"></span>
        </span>
        <span>{{ $panelBadge }}</span>
    </div>

    {{-- Libellé de contexte si réinitialisation ou création --}}
    @if($isRegister)
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Rejoignez le réseau panafricain et pilotez vos promotions d'incubation.
        </p>
    @elseif($isReset)
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Saisissez votre adresse email pour recevoir vos instructions sécurisées.
        </p>
    @else
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Identifiez-vous pour accéder à vos outils et données certifiées.
        </p>
    @endif
</div>
