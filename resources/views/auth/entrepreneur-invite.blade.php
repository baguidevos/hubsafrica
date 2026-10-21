<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Entrepreneur &bull; Hub Africa</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased relative overflow-x-hidden selection:bg-[#F8B81D] selection:text-slate-950">
    
    {{-- Arrière-plan avec halos lumineux --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-36 -left-36 w-96 h-96 bg-teal-600/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-36 w-96 h-96 bg-[#F8B81D]/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 left-1/3 w-80 h-80 bg-emerald-900/25 rounded-full blur-3xl"></div>
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

            <div class="inline-flex items-center gap-2 text-xs text-slate-300 bg-slate-900/80 border border-slate-800 px-3 py-1.5 rounded-full">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Invitation sécurisée &bull; <strong>{{ $sae->name }}</strong></span>
            </div>
        </div>
    </header>

    {{-- Contenu principal --}}
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10 my-6">
        <div class="max-w-xl w-full bg-white dark:bg-slate-900/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-10 space-y-6">
            
            {{-- En-tête --}}
            <div class="text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-teal-500/10 text-teal-700 dark:text-teal-400 border border-teal-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Invitation Entrepreneur</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Finaliser mon inscription
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto">
                    La structure <strong>{{ $sae->name }}</strong> vous invite à intégrer son écosystème d'accompagnement sur Hub Africa.
                </p>
            </div>

            {{-- Bannière cohorte si rattaché --}}
            @if ($cohort)
                <div class="rounded-2xl bg-teal-500/10 border border-teal-500/30 p-4 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-teal-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-teal-800 dark:text-teal-300">
                            Intégration directe à la cohorte
                        </div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">
                            {{ $cohort->name }}
                            @if ($cohort->program)
                                <span class="font-normal text-xs text-slate-500 dark:text-slate-400">({{ $cohort->program->name }})</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-teal-700/80 dark:text-teal-400/80 mt-0.5">
                            Votre dossier sera immédiatement activé au sein de cette promotion en cours.
                        </div>
                    </div>
                </div>
            @endif

            {{-- Message personnalisé si présent --}}
            @if ($invitation->message)
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Mot de la structure :
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 italic">
                        « {{ $invitation->message }} »
                    </p>
                </div>
            @endif

            {{-- Affichage des erreurs --}}
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

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('entrepreneur.invite.register', $invitation->token) }}" class="space-y-5">
                @csrf

                {{-- Email verrouillé --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Adresse Email invitée
                    </label>
                    <input 
                        type="email" 
                        value="{{ $invitation->email }}" 
                        disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 text-sm cursor-not-allowed font-mono"
                    >
                </div>

                {{-- Identité --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Prénom <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="{{ old('first_name', $invitation->first_name) }}" 
                            required 
                            placeholder="Ex: Awa"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        >
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nom de famille <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="{{ old('last_name', $invitation->last_name) }}" 
                            required 
                            placeholder="Ex: Diop"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Numéro de téléphone
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $invitation->phone) }}" 
                        placeholder="+221 77 000 00 00"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                    >
                </div>

                {{-- Informations Projet --}}
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Votre Projet d'Entreprise
                    </div>

                    <div>
                        <label for="project_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nom du projet / Startup
                        </label>
                        <input 
                            type="text" 
                            id="project_name" 
                            name="project_name" 
                            value="{{ old('project_name', $invitation->project_name) }}" 
                            placeholder="Ex: PayTech Solutions"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="project_sector" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Secteur d'activité
                            </label>
                            <input 
                                type="text" 
                                id="project_sector" 
                                name="project_sector" 
                                value="{{ old('project_sector') }}" 
                                placeholder="Ex: FinTech, AgriTech, Santé..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            >
                        </div>
                        <div>
                            <label for="project_description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Brève description
                            </label>
                            <input 
                                type="text" 
                                id="project_description" 
                                name="project_description" 
                                value="{{ old('project_description') }}" 
                                placeholder="Ex: Solution de paiement digitalisée..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            >
                        </div>
                    </div>
                </div>

                {{-- Mot de passe --}}
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Sécurité de votre compte
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Définir un mot de passe <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8" 
                                placeholder="8 caractères minimum"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Confirmer le mot de passe <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required 
                                placeholder="Répétez le mot de passe"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            >
                        </div>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-3.5 px-6 rounded-2xl bg-teal-600 hover:bg-teal-500 text-white font-extrabold text-sm tracking-wide shadow-lg shadow-teal-600/30 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>Activer mon compte &amp; Accéder à mon espace</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>
    </main>

    {{-- Pied de page --}}
    <footer class="w-full border-t border-slate-800/80 bg-slate-950/70 backdrop-blur-md relative z-10 py-4 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Hub Africa &bull; Tous droits réservés.
    </footer>
</body>
</html>
