<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Invalide &bull; Hub Africa</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased relative overflow-x-hidden">
    
    {{-- Halos d'ambiance --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-36 -left-36 w-96 h-96 bg-teal-600/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-36 w-96 h-96 bg-[#F8B81D]/20 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:28px_28px] opacity-15"></div>
    </div>

    {{-- Bandeau supérieur --}}
    <header class="w-full border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-md relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 text-slate-300 hover:text-[#F8B81D] text-xs font-semibold transition">
                <svg class="w-4 h-4 text-[#F8B81D] transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-bold">Hub Africa</span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span class="text-slate-400 font-normal hidden sm:inline">Portail Entrepreneur</span>
            </a>
        </div>
    </header>

    {{-- Contenu central --}}
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10 my-6">
        <div class="max-w-md w-full bg-white dark:bg-slate-900/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-8 sm:p-10 space-y-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500/15 text-[#F8B81D] border border-[#F8B81D]/30 mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <div class="space-y-2">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Lien d'invitation expiré ou invalide
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $message ?? 'Ce lien d\'invitation a déjà été utilisé ou a expiré. Veuillez contacter votre structure d\'accompagnement pour demander un nouveau lien d\'accès.' }}
                </p>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Retour à l'accueil
                </a>
                <a href="{{ route('public.programmes') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-xs transition">
                    Explorer les programmes
                </a>
            </div>
        </div>
    </main>

    {{-- Pied de page --}}
    <footer class="w-full border-t border-slate-800/80 bg-slate-950/70 backdrop-blur-md relative z-10 py-4 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Hub Africa &bull; Tous droits réservés.
    </footer>
</body>
</html>
