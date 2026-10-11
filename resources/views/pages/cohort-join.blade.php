<x-layouts.public>
    @php
        $isClosed = $cohort->status === \App\Enums\CohortStatus::CLOTUREE;
        $isOpen = $cohort->status === \App\Enums\CohortStatus::OUVERTE;
    @endphp

    {{-- Bannière En-tête --}}
    <section class="bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 text-white py-12 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Fil d'ariane --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D] transition">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.programmes') }}" class="hover:text-[#F8B81D] transition">Programmes</a>
                <span>/</span>
                <a href="{{ route('public.programme.detail', $cohort->program_id) }}" class="hover:text-[#F8B81D] transition">{{ $cohort->program->name }}</a>
                <span>/</span>
                <span class="text-slate-200">Rejoindre la promotion</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        @if($isOpen)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-[#F8B81D] border border-amber-500/30">
                                <span class="w-2 h-2 rounded-full bg-[#F8B81D] animate-pulse"></span>
                                Inscriptions Ouvertes
                            </span>
                        @elseif($isClosed)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                Promotion Clôturée
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                En cours de sélection
                            </span>
                        @endif

                        @if($cohort->program->level)
                            <span class="text-xs font-semibold px-3 py-1 bg-slate-800 text-slate-300 border border-slate-700 rounded-lg">
                                Phase : {{ $cohort->program->level->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        {{ $cohort->name }}
                    </h1>

                    @php
                        $hostSae = $cohort->sae ?: $cohort->program?->sae;
                    @endphp
                    <p class="text-xs sm:text-sm text-slate-400 flex items-center gap-2">
                        <span>Programme : <strong class="text-white">{{ $cohort->program->name }}</strong></span>
                        <span>&bull;</span>
                        <span class="inline-flex items-center gap-1.5">
                            @if($hostSae?->logo_url)
                                <img src="{{ $hostSae->logo_url }}" alt="{{ $hostSae->name }}" class="w-4 h-4 rounded object-contain bg-white border border-slate-700 shrink-0">
                            @endif
                            <span>Organisé par <strong class="text-[#F8B81D]">{{ $hostSae?->name }}</strong></span>
                        </span>
                    </p>
                    @if($hostSae && $hostSae->types->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($hostSae->types as $t)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-slate-200 border border-white/20">
                                    {{ $t->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Badges Dates & Capacité --}}
                <div class="flex flex-wrap items-center gap-3 bg-slate-800/80 p-4 rounded-2xl border border-slate-700 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Démarrage</div>
                        <div class="font-bold text-slate-100">{{ $cohort->start_date ? $cohort->start_date->format('d/m/Y') : 'À venir' }}</div>
                    </div>
                    <div class="w-px h-8 bg-slate-700 mx-2"></div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Clôture</div>
                        <div class="font-bold text-slate-100">{{ $cohort->end_date ? $cohort->end_date->format('d/m/Y') : 'À déterminer' }}</div>
                    </div>
                    <div class="w-px h-8 bg-slate-700 mx-2"></div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Capacité</div>
                        <div class="font-bold text-[#F8B81D]">{{ $cohort->capacity ?: 20 }} startups</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contenu Principal --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Alerte d'état si erreurs générales --}}
        @if($errors->has('general'))
            <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ $errors->first('general') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($isClosed)
            {{-- Cas d'une promotion clôturée --}}
            <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 sm:p-12 text-center border border-slate-200 shadow-sm space-y-6">
                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto text-rose-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Les candidatures pour cette promotion sont closes</h2>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        La cohorte <strong>{{ $cohort->name }}</strong> du programme <em>{{ $cohort->program->name }}</em> a atteint sa date limite de sélection ou sa capacité maximale.
                    </p>
                </div>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('public.programme.detail', $cohort->program_id) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                        Voir la fiche du programme
                    </a>
                    <a href="{{ route('public.programmes') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-sm transition">
                        Découvrir d'autres programmes disponibles &rarr;
                    </a>
                </div>
            </div>
        @else
            {{-- Promotion Ouverte : Deux colonnes claires (Connexion vs Inscription) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Colonne 1 : Déjà un compte Hub Africa ? (5 cols) --}}
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="space-y-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#2F3284]/10 text-[#2F3284]">
                            Porteur de projet déjà inscrit
                        </span>
                        <h2 class="text-xl font-black text-slate-900">J'ai déjà un compte</h2>
                        <p class="text-xs text-slate-500">
                            Connectez-vous pour pré-remplir automatiquement votre profil et accéder directement au formulaire de cette cohorte.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('public.cohort.login', $cohort->id) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="login_email" class="block text-xs font-bold text-slate-700 mb-1">
                                Adresse Email
                            </label>
                            <input
                                id="login_email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="votre.email@domaine.com"
                                class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                            >
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="login_password" class="block text-xs font-bold text-slate-700 mb-1">
                                Mot de passe
                            </label>
                            <input
                                id="login_password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                            >
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#2F3284] focus:ring-[#F8B81D]">
                                <span>Se souvenir de moi</span>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3.5 px-4 rounded-xl bg-[#2F3284] hover:bg-[#25286a] text-white text-xs font-bold shadow-md transition flex items-center justify-center gap-2"
                        >
                            <span>Se connecter &amp; Postuler</span>
                            <svg class="w-4 h-4 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <div class="pt-4 border-t border-slate-100 text-center">
                        <span class="text-[11px] text-slate-400">
                            Mot de passe oublié ? Contactez l'incubateur organisateur pour réinitialiser vos accès.
                        </span>
                    </div>
                </div>

                {{-- Colonne 2 : Nouveau candidat ? Créer mon compte (7 cols) --}}
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border-2 border-[#F8B81D]/50 shadow-md space-y-6 relative overflow-hidden">
                    {{-- Pastille Promo --}}
                    <div class="absolute top-0 right-0 bg-[#F8B81D] text-slate-950 text-[10px] font-black uppercase px-4 py-1 rounded-bl-xl tracking-wider">
                        Recommandé si vous n'avez pas de compte
                    </div>

                    <div class="space-y-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#F8B81D]/20 text-slate-900">
                            Nouveau Porteur de Projet
                        </span>
                        <h2 class="text-xl font-black text-slate-900">Créer mon compte candidat</h2>
                        <p class="text-xs text-slate-500">
                            Renseignez vos coordonnées de base pour ouvrir votre espace personnel et débuter immédiatement votre candidature pour <strong>{{ $cohort->name }}</strong>.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('public.cohort.register', $cohort->id) }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="reg_first_name" class="block text-xs font-bold text-slate-700 mb-1">
                                    Prénom <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_first_name"
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    required
                                    placeholder="Ex: Amadou"
                                    class="w-full px-4 py-2.5 rounded-xl border @error('first_name') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                                @error('first_name')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reg_last_name" class="block text-xs font-bold text-slate-700 mb-1">
                                    Nom de famille <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_last_name"
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    required
                                    placeholder="Ex: Diallo"
                                    class="w-full px-4 py-2.5 rounded-xl border @error('last_name') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                                @error('last_name')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="reg_email" class="block text-xs font-bold text-slate-700 mb-1">
                                    Adresse Email <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="amadou.diallo@startup.africa"
                                    class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                                @error('email')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reg_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                    Numéro de Téléphone (WhatsApp) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_phone"
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    required
                                    placeholder="+221 77 000 00 00"
                                    class="w-full px-4 py-2.5 rounded-xl border @error('phone') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                                @error('phone')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="reg_password" class="block text-xs font-bold text-slate-700 mb-1">
                                    Mot de passe <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Au moins 8 caractères"
                                    class="w-full px-4 py-2.5 rounded-xl border @error('password') border-rose-300 bg-rose-50/50 @else border-slate-300 @enderror focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                                @error('password')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reg_password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                                    Confirmer le mot de passe <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="reg_password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Répétez le mot de passe"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                >
                            </div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="w-full py-3.5 px-4 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 text-xs font-extrabold shadow-md transition flex items-center justify-center gap-2 transform hover:-translate-y-0.5"
                            >
                                <span>Créer mon compte &amp; Débuter ma candidature</span>
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>

                        <p class="text-[11px] text-slate-500 text-center pt-2">
                            En créant votre compte, vous serez automatiquement rattaché à l'incubateur <strong>{{ $cohort->sae?->name ?: $cohort->program->sae?->name }}</strong> et redirigé vers les étapes du dossier de candidature.
                        </p>
                    </form>
                </div>
            </div>
        @endif

        {{-- Détails Pédagogiques & Objectifs de la promotion --}}
        <div class="mt-12 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#2F3284]"></span>
                À propos de la promotion {{ $cohort->name }}
            </h3>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                {{ $cohort->objectives ?: $cohort->program->description ?: 'Cette promotion vise à accélérer la trajectoire de croissance des projets sélectionnés à travers un suivi personnalisé, des ateliers experts et des évaluations périodiques selon les jalons méthodologiques définis.' }}
            </p>
        </div>

    </section>
</x-layouts.public>
