<div class="max-w-3xl mx-auto">
    @if($isSubmitted)
        {{-- Écran de Confirmation Officielle & Succès --}}
        <div class="bg-white rounded-3xl p-8 sm:p-12 border border-amber-200 shadow-xl text-center space-y-6">
            <div class="w-20 h-20 bg-amber-50 text-[#F8B81D] rounded-full flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div class="space-y-2">
                <span class="inline-block px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-[#2F3284] border border-amber-200">
                    Candidature Enregistrée avec Succès
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                    Félicitations, votre dossier a été transmis !
                </h2>
                <p class="text-sm text-slate-600 max-w-lg mx-auto">
                    Votre candidature pour le programme <strong class="text-slate-900 font-semibold">{{ $program->name }}</strong> a été officiellement transmise au comité de sélection de la structure <strong class="text-slate-900 font-semibold">{{ $program->sae?->name }}</strong>.
                </p>
            </div>

            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-3 text-xs text-slate-700">
                <div class="font-bold text-slate-900 text-sm border-b border-slate-200 pb-2 flex items-center justify-between">
                    <span>Récapitulatif de votre soumission</span>
                    <span class="text-[#2F3284]">Statut : En cours d'examen</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-400 block">Projet candidat :</span>
                        <strong class="text-slate-900 text-sm">{{ $project_name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Structure d'accueil (SAE) :</span>
                        <strong class="text-slate-900 text-sm">{{ $program->sae?->name }}</strong>
                    </div>
                </div>
                <p class="text-slate-500 pt-2">
                    Un email de confirmation vous a été envoyé. Vous pouvez dès à présent accéder à votre espace entrepreneur pour suivre l'instruction de votre dossier et compléter votre data room.
                </p>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                    href="/portal"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-sm shadow-md transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
                >
                    <span>Accéder à mon Espace Entrepreneur</span>
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a
                    href="{{ route('public.programmes') }}"
                    class="w-full sm:w-auto px-6 py-3.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-sm transition"
                >
                    Retour aux programmes
                </a>
            </div>
        </div>
    @else
        {{-- Wizard de Candidature Multi-étapes --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg overflow-hidden">
            
            {{-- En-tête du Formulaire & Indicateur d'Étapes --}}
            <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-950 to-[#2F3284] text-white border-b border-[#2F3284]/40">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#F8B81D]">Dossier Officiel de Candidature</span>
                        <h1 class="text-xl sm:text-2xl font-black mt-1">{{ $program->name }}</h1>
                    </div>
                    <div class="hidden sm:block text-right">
                        <span class="text-xs text-slate-400 block">Structure Organisatrice</span>
                        <span class="font-bold text-sm text-amber-200">{{ $program->sae?->name }}</span>
                    </div>
                </div>

                {{-- Progression des étapes --}}
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <button
                        type="button"
                        wire:click="goToStep(1)"
                        class="p-2.5 rounded-xl flex items-center gap-2 transition {{ $currentStep === 1 ? 'bg-[#F8B81D] text-slate-950 font-bold' : ($currentStep > 1 ? 'bg-[#2F3284] text-amber-200' : 'bg-slate-800/60 text-slate-400') }}"
                    >
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black {{ $currentStep >= 1 ? 'bg-white text-slate-900' : 'bg-slate-700 text-slate-300' }}">1</span>
                        <span class="truncate">Porteur</span>
                    </button>

                    <button
                        type="button"
                        wire:click="goToStep(2)"
                        class="p-2.5 rounded-xl flex items-center gap-2 transition {{ $currentStep === 2 ? 'bg-[#F8B81D] text-slate-950 font-bold' : ($currentStep > 2 ? 'bg-[#2F3284] text-amber-200' : 'bg-slate-800/60 text-slate-400') }}"
                    >
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black {{ $currentStep >= 2 ? 'bg-white text-slate-900' : 'bg-slate-700 text-slate-300' }}">2</span>
                        <span class="truncate">Startup &amp; Projet</span>
                    </button>

                    <button
                        type="button"
                        wire:click="goToStep(3)"
                        class="p-2.5 rounded-xl flex items-center gap-2 transition {{ $currentStep === 3 ? 'bg-[#F8B81D] text-slate-950 font-bold' : 'bg-slate-800/60 text-slate-400' }}"
                    >
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black {{ $currentStep === 3 ? 'bg-white text-slate-900' : 'bg-slate-700 text-slate-300' }}">3</span>
                        <span class="truncate">Motivation &amp; Pièces</span>
                    </button>
                </div>
            </div>

            @if($selectedCohort)
                <div class="mx-6 sm:mx-10 mt-6 -mb-2 p-4 rounded-2xl bg-amber-50 border border-amber-200/90 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#F8B81D] text-slate-950 font-black flex items-center justify-center text-sm shadow-sm shrink-0">
                            🎯
                        </span>
                        <div>
                            <div class="text-[10px] font-bold text-amber-900 uppercase tracking-wide">Promotion ciblée</div>
                            <div class="text-sm font-extrabold text-slate-900">{{ $selectedCohort->name }}</div>
                        </div>
                    </div>
                    <div class="text-right text-xs text-slate-600 hidden sm:block">
                        <span>Lancement : <strong>{{ $selectedCohort->start_date ? $selectedCohort->start_date->format('d/m/Y') : 'À venir' }}</strong></span>
                        <span class="mx-1">&bull;</span>
                        <span class="text-amber-800 font-bold">{{ $selectedCohort->capacity ?: 20 }} places</span>
                    </div>
                </div>
            @endif

            {{-- Corps du Formulaire --}}
            <form wire:submit.prevent="submitApplication" class="p-6 sm:p-10 space-y-8">
                @if($errors->has('submit_error'))
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                        {{ $errors->first('submit_error') }}
                    </div>
                @endif

                {{-- ÉTAPE 1 : Identité de l'Entrepreneur --}}
                @if($currentStep === 1)
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-bold text-slate-900">1. Identité et Contact du Porteur de Projet</h2>
                            <p class="text-xs text-slate-500">Renseignez vos coordonnées directes pour le suivi de votre candidature.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prénom *</label>
                                <input
                                    type="text"
                                    wire:model="first_name"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="Ex: Aminata"
                                >
                                @error('first_name') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom de famille *</label>
                                <input
                                    type="text"
                                    wire:model="last_name"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="Ex: Ndiaye"
                                >
                                @error('last_name') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Adresse Email professionnelle *</label>
                                <input
                                    type="email"
                                    wire:model="email"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="aminata.ndiaye@startup.sn"
                                >
                                @error('email') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Téléphone / WhatsApp *</label>
                                <input
                                    type="text"
                                    wire:model="phone"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="+221 77 000 00 00"
                                >
                                @error('phone') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Genre *</label>
                                <select
                                    wire:model="gender"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm bg-white outline-none transition"
                                >
                                    <option value="M">Homme</option>
                                    <option value="F">Femme</option>
                                </select>
                                @error('gender') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ville &amp; Pays de résidence</label>
                                <input
                                    type="text"
                                    wire:model="city"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="Ex: Dakar, Sénégal"
                                >
                            </div>
                        </div>

                        @guest
                            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-4">
                                <div class="text-xs font-bold text-amber-900">
                                    Création de votre accès sécurisé à l'Espace Entrepreneur
                                </div>
                                <p class="text-[11px] text-amber-800">
                                    Un mot de passe vous permettra de suivre l'avancement de votre dossier et d'échanger avec votre futur coach.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Mot de passe *</label>
                                        <input
                                            type="password"
                                            wire:model="password"
                                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs outline-none bg-white"
                                            placeholder="Min. 8 caractères"
                                        >
                                        @error('password') <span class="text-[10px] text-red-600 font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Confirmez le mot de passe *</label>
                                        <input
                                            type="password"
                                            wire:model="password_confirmation"
                                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs outline-none bg-white"
                                            placeholder="Répétez le mot de passe"
                                        >
                                    </div>
                                </div>
                            </div>
                        @endguest

                        <div class="flex justify-end pt-4">
                            <button
                                type="button"
                                wire:click="goToStep(2)"
                                class="px-6 py-3 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-md transition flex items-center gap-2"
                            >
                                <span>Étape suivante : Le Projet</span>
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- ÉTAPE 2 : Présentation du Projet --}}
                @if($currentStep === 2)
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-bold text-slate-900">2. Présentation du Projet / Startup</h2>
                            <p class="text-xs text-slate-500">Décrivez la solution innovante que vous développez.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom de la Startup / Projet *</label>
                                <input
                                    type="text"
                                    wire:model="project_name"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                    placeholder="Ex: AgriTech Sahel"
                                >
                                @error('project_name') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Secteur d'activité *</label>
                                <select
                                    wire:model="sector"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm bg-white outline-none transition"
                                >
                                    <option value="">Sélectionnez un secteur</option>
                                    <option value="AgriTech / Agriculture durable">AgriTech &amp; Agriculture durable</option>
                                    <option value="FinTech / Inclusion financière">FinTech &amp; Inclusion financière</option>
                                    <option value="HealthTech / Santé">HealthTech &amp; Santé</option>
                                    <option value="EdTech / Formation & Emploi">EdTech &amp; Éducation</option>
                                    <option value="CleanTech / Énergie & Climat">CleanTech &amp; Énergie verte</option>
                                    <option value="E-commerce & Logistique">E-commerce &amp; Logistique</option>
                                    <option value="Artisanat & Industrie créative">Artisanat &amp; ICC</option>
                                    <option value="Autre secteur innovant">Autre secteur</option>
                                </select>
                                @error('sector') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pitch synthétique de votre projet *</label>
                            <textarea
                                wire:model="pitch"
                                rows="4"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                placeholder="Quel problème résolvez-vous ? Quelle est votre proposition de valeur et qui sont vos clients cibles ?"
                            ></textarea>
                            @error('pitch') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Taille de l'équipe (personnes)</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="50"
                                    wire:model="team_size"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm outline-none"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Site web ou Page LinkedIn (facultatif)</label>
                                <input
                                    type="text"
                                    wire:model="website"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm outline-none"
                                    placeholder="https://monprojet.africa"
                                >
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                wire:click="goToStep(1)"
                                class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition"
                            >
                                &larr; Retour
                            </button>
                            <button
                                type="button"
                                wire:click="goToStep(3)"
                                class="px-6 py-3 rounded-xl bg-[#2F3284] hover:bg-[#25286b] text-white font-bold text-xs shadow-md transition flex items-center gap-2"
                            >
                                <span>Étape suivante : Dossier &amp; Motivation</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- ÉTAPE 3 : Motivation & Pièces Jointes --}}
                @if($currentStep === 3)
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-bold text-slate-900">3. Motivation &amp; Dépôt du Dossier</h2>
                            <p class="text-xs text-slate-500">Expliquez pourquoi ce programme est déterminant pour le développement de votre startup.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pourquoi postulez-vous à ce programme en particulier ? *</label>
                            <textarea
                                wire:model="motivation"
                                rows="4"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition"
                                placeholder="Vos attentes pédagogiques, votre disponibilité pour suivre les ateliers et les séances de mentorat individuel..."
                            ></textarea>
                            @error('motivation') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Vos principaux besoins prioritaires</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-700">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model="needs" value="Mentorat & Structuration" class="rounded text-[#2F3284] focus:ring-[#F8B81D]">
                                    <span>Mentorat méthodologique &amp; Stratégie</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model="needs" value="Accès aux marchés & Ventes" class="rounded text-[#2F3284] focus:ring-[#F8B81D]">
                                    <span>Accès aux marchés &amp; B2B</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model="needs" value="Levée de fonds & Financement" class="rounded text-[#2F3284] focus:ring-[#F8B81D]">
                                    <span>Préparation à la levée de fonds</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model="needs" value="Expertise technique & MVP" class="rounded text-[#2F3284] focus:ring-[#F8B81D]">
                                    <span>Finalisation du MVP / R&amp;D</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pitch Deck / Présentation du Projet (PDF ou PPTX, max 15 Mo)
                            </label>
                            <input
                                type="file"
                                wire:model="pitch_deck"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-[#2F3284] hover:file:bg-amber-100 cursor-pointer border border-slate-300 rounded-xl"
                            >
                            <div wire:loading wire:target="pitch_deck" class="text-[11px] text-[#2F3284] font-medium mt-1">
                                Téléversement du fichier en cours...
                            </div>
                            @error('pitch_deck') <span class="text-[11px] text-red-600 font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- QUESTIONS COMPLÉMENTAIRES DÉFINIES PAR LA STRUCTURE (TEMPLATE PERSONNALISÉ) --}}
                        @if(!empty($custom_fields))
                            <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2F3284]"></span>
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                        Questions spécifiques posées par {{ $program->sae?->name ?? 'la structure d\'accompagnement' }}
                                    </h3>
                                </div>
                                <p class="text-[11px] text-slate-600">
                                    Merci de renseigner ces informations complémentaires requises par l'équipe d'accompagnement pour évaluer votre candidature.
                                </p>

                                <div class="space-y-4 pt-2">
                                    @foreach($custom_fields as $field)
                                        @php
                                            $fData = $field['data'] ?? [];
                                            $fKey = $fData['key'] ?? $fData['name'] ?? $field['name'] ?? null;
                                            $fType = $field['type'] ?? 'text';
                                            $fLabel = $fData['label'] ?? $field['label'] ?? ucfirst(str_replace('_', ' ', (string) $fKey));
                                            $fReq = !empty($fData['required']);
                                            $fPlaceholder = $fData['placeholder'] ?? '';
                                            $fOptions = $fData['options'] ?? [];
                                        @endphp

                                        @if($fKey)
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                                    {{ $fLabel }} @if($fReq)<span class="text-red-500 font-bold">*</span>@endif
                                                </label>

                                                @if($fType === 'textarea')
                                                    <textarea
                                                        wire:model="custom_answers.{{ $fKey }}"
                                                        rows="3"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition bg-white"
                                                        placeholder="{{ $fPlaceholder }}"
                                                    ></textarea>
                                                @elseif($fType === 'select')
                                                    <select
                                                        wire:model="custom_answers.{{ $fKey }}"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm bg-white outline-none transition"
                                                    >
                                                        <option value="">-- Sélectionnez une option --</option>
                                                        @foreach($fOptions as $optVal => $optLabel)
                                                            @php
                                                                $val = is_array($optLabel) ? ($optLabel['value'] ?? $optVal) : (is_numeric($optVal) ? $optLabel : $optVal);
                                                                $lbl = is_array($optLabel) ? ($optLabel['label'] ?? $optLabel['value'] ?? $optVal) : $optLabel;
                                                            @endphp
                                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($fType === 'number')
                                                    <input
                                                        type="number"
                                                        wire:model="custom_answers.{{ $fKey }}"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition bg-white"
                                                        placeholder="{{ $fPlaceholder }}"
                                                    >
                                                @elseif($fType === 'checkbox')
                                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            wire:model="custom_answers.{{ $fKey }}"
                                                            class="rounded text-[#2F3284] focus:ring-[#F8B81D]"
                                                        >
                                                        <span class="text-xs text-slate-700">{{ $fPlaceholder ?: 'Oui / Confirmé' }}</span>
                                                    </label>
                                                @else
                                                    <input
                                                        type="text"
                                                        wire:model="custom_answers.{{ $fKey }}"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-[#F8B81D] focus:ring-2 focus:ring-[#F8B81D]/20 text-sm outline-none transition bg-white"
                                                        placeholder="{{ $fPlaceholder }}"
                                                    >
                                                @endif

                                                @error("custom_answers.{$fKey}")
                                                    <span class="text-[11px] text-red-600 font-medium block mt-1">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-2">
                            <div class="font-bold text-slate-800">Engagement &amp; Exactitude des données</div>
                            <p class="text-[11px]">
                                En validant ce formulaire, vous certifiez l'exactitude des informations fournies et acceptez qu'elles soient traitées par l'équipe de la structure d'accompagnement <strong>{{ $program->sae?->name }}</strong> dans le cadre du processus de sélection et d'évaluation des candidatures.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                wire:click="goToStep(2)"
                                class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition"
                            >
                                &larr; Retour
                            </button>
                            <button
                                type="submit"
                                class="px-7 py-3 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-900 font-extrabold text-sm shadow-md transition transform hover:-translate-y-0.5 flex items-center gap-2"
                            >
                                <span wire:loading.remove wire:target="submitApplication">Transmettre ma Candidature Officielle</span>
                                <span wire:loading wire:target="submitApplication">Enregistrement en cours...</span>
                                <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    @endif
</div>
