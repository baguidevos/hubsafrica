<x-layouts.public>
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-[#F8B81D] border border-[#F8B81D]/30 text-xs font-semibold">
                Réseau des Mentors &amp; Experts
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Rejoindre une SAE comme <span class="text-[#F8B81D]">Coach Référent</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Mettez votre expertise métier au service des startups à fort potentiel en postulant auprès d'un incubateur ou accélérateur partenaire.
            </p>
        </div>
    </section>

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-10">
            @if(session('status_success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                    {{ session('status_success') }}
                </div>
            @endif

            <form action="{{ route('public.coach.join.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Choix de la SAE -->
                <div>
                    <label for="sae_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Structure d'accompagnement (SAE) ciblée <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="sae_id"
                        id="sae_id"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                    >
                        <option value="">Sélectionnez l'incubateur ou hub à intégrer...</option>
                        @foreach($saes as $sae)
                            <option value="{{ $sae->id }}" {{ (old('sae_id', $selectedSaeId) == $sae->id) ? 'selected' : '' }}>
                                {{ $sae->name }} ({{ $sae->city ?? $sae->country ?? 'Afrique' }})
                            </option>
                        @endforeach
                    </select>
                    @error('sae_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Nom &amp; Prénom <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="Ex: Jean Koffi"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Adresse Email <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="votre.email@domaine.com"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Téléphone -->
                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Numéro de Téléphone / WhatsApp
                        </label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            placeholder="+228 90 00 00 00"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                        >
                        @error('phone')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- LinkedIn -->
                    <div>
                        <label for="linkedin_url" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Profil LinkedIn
                        </label>
                        <input
                            type="url"
                            name="linkedin_url"
                            id="linkedin_url"
                            value="{{ old('linkedin_url') }}"
                            placeholder="https://linkedin.com/in/votre-profil"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                        >
                        @error('linkedin_url')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Spécialités -->
                <div>
                    <label for="specialties" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Domaines d'expertise / Spécialités (séparés par des virgules)
                    </label>
                    <input
                        type="text"
                        name="specialties"
                        id="specialties"
                        value="{{ old('specialties') }}"
                        placeholder="Ex: Levée de fonds, Stratégie Go-To-Market, Agritech, FinTech, Juridique..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                    >
                    @error('specialties')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bio & Motivation -->
                <div>
                    <label for="bio" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Expérience &amp; Motivations <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="bio"
                        id="bio"
                        rows="5"
                        required
                        placeholder="Résumez votre parcours, vos réalisations et la manière dont vous souhaitez faire grandir les porteurs de projet..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-[#2F3284] focus:ring-2 focus:ring-[#2F3284]/20 text-sm text-slate-800 transition"
                    >{{ old('bio') }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bouton de soumission -->
                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full py-3.5 rounded-xl bg-[#2F3284] hover:bg-[#1e2167] text-white font-bold text-sm text-center shadow-md transition cursor-pointer"
                    >
                        Transmettre ma candidature de Mentor &rarr;
                    </button>
                    <p class="mt-3 text-center text-xs text-slate-500">
                        Votre demande sera soumise aux responsables de la structure choisie. Dès validation, un lien d'activation sécurisé vous sera envoyé par email.
                    </p>
                </div>
            </form>
        </div>
    </section>
</x-layouts.public>
