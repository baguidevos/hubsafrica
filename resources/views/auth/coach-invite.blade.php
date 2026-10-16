<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Création de compte Coach &bull; Hub Africa</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased relative overflow-x-hidden selection:bg-[#F8B81D] selection:text-slate-950">
    
    {{-- Arrière-plan d'ambiance avec halos lumineux Hub Africa --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-36 -left-36 w-96 h-96 bg-[#2F3284]/35 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-36 w-96 h-96 bg-[#F8B81D]/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 left-1/3 w-80 h-80 bg-indigo-900/20 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:28px_28px] opacity-15"></div>
    </div>

    {{-- Bandeau supérieur avec retour --}}
    <header class="w-full border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-md relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 text-slate-300 hover:text-[#F8B81D] text-xs font-semibold transition">
                <svg class="w-4 h-4 text-[#F8B81D] transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-bold">Hub Africa</span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span class="text-slate-400 font-normal hidden sm:inline">Portail Panafricain</span>
            </a>

            <div class="inline-flex items-center gap-2 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Invitation certifiée</span>
            </div>
        </div>
    </header>

    {{-- Contenu principal : Carte d'inscription --}}
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10 my-6">
        <div class="max-w-lg w-full bg-white dark:bg-slate-900/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-10 space-y-6">
            
            {{-- En-tête de la carte --}}
            <div class="text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#F8B81D]/15 text-[#2F3284] dark:text-[#F8B81D] border border-[#F8B81D]/30 shadow-xs">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F8B81D] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F8B81D]"></span>
                    </span>
                    <span>Invitation Coach &bull; {{ $sae->name }}</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Finaliser mon compte Coach
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm mx-auto">
                    Vous avez été formellement invité(e) par la structure <strong>{{ $sae->name }}</strong> à rejoindre le collège des mentors et experts d'accompagnement.
                </p>
            </div>

            {{-- Affichage des erreurs de validation --}}
            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 border border-rose-200 dark:border-rose-800/80">
                    <div class="flex items-center gap-2 text-rose-800 dark:text-rose-300 font-bold text-xs mb-1">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Merci de corriger les éléments suivants :</span>
                    </div>
                    <ul class="text-xs text-rose-700 dark:text-rose-400 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulaire de finalisation --}}
            <form method="POST" action="{{ route('coach.invite.register', $invitation->token) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Adresse Email (invitée et réservée)
                    </label>
                    <input 
                        type="email" 
                        value="{{ $invitation->email }}" 
                        disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 text-sm cursor-not-allowed font-mono"
                    >
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nom complet &amp; Titre <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $invitation->name) }}" 
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                        placeholder="Ex: Dr. Kouamé Koffi"
                    >
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Numéro de téléphone / WhatsApp
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                        placeholder="Ex: +225 07 00 00 00 00"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Mot de passe <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            minlength="8"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                            placeholder="Min. 8 caractères"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Confirmation <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            minlength="8"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                            placeholder="Répétez"
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="submit"
                        class="w-full py-3.5 px-5 bg-[#2F3284] hover:bg-[#25286a] text-white font-bold text-sm rounded-xl shadow-lg shadow-[#2F3284]/25 hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
                    >
                        <span>Créer mon compte Coach &amp; Mentor</span>
                        <svg class="w-4 h-4 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-400 space-y-1">
                <div>Plateforme Hub Africa &bull; Réseau Panafricain des Structures d'Accompagnement</div>
                <div class="text-[11px] text-slate-500">Besoin d'assistance ? {{ config('hubafrica.contact.email') }}</div>
            </div>
        </div>
    </main>

    {{-- Pied de page minimaliste --}}
    <footer class="w-full border-t border-slate-800/80 py-4 text-center text-xs text-slate-500 relative z-10">
        &copy; {{ date('Y') }} Hub Africa &bull; Tous droits réservés.
    </footer>
</body>
</html>
