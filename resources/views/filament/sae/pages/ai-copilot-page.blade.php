<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Barre de commande Copilote IA --}}
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Assistant Copilote — Cartes Interactives</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Décrivez ce que vous souhaitez faire (ex : « Je veux créer un programme ») pour afficher immédiatement le formulaire ou la vue interactive adaptée.
                </p>
            </div>

            <form wire:submit="submitPrompt" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    wire:model="prompt"
                    placeholder="Ex: Je veux créer un programme d'accélération..."
                    class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white focus:outline-none focus:border-[#2F3284]"
                />
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-[#2F3284] hover:bg-[#24276b] text-white text-xs font-bold transition shrink-0 cursor-pointer"
                >
                    Générer l'interface
                </button>
            </form>

            {{-- Raccourcis rapides --}}
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <button
                    type="button"
                    wire:click="submitPrompt('Créer un programme')"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition cursor-pointer {{ $activeWidget === 'program_creator' ? 'bg-[#2F3284] text-white border-[#2F3284]' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100' }}"
                >
                    + Créer un programme
                </button>
                <button
                    type="button"
                    wire:click="submitPrompt('Créer une cohorte')"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition cursor-pointer {{ $activeWidget === 'cohort_creator' ? 'bg-[#2F3284] text-white border-[#2F3284]' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100' }}"
                >
                    + Créer une cohorte
                </button>
                <button
                    type="button"
                    wire:click="submitPrompt('Afficher les métriques SAE')"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition cursor-pointer {{ $activeWidget === 'metrics_dashboard' ? 'bg-[#2F3284] text-white border-[#2F3284]' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100' }}"
                >
                    Tableau de bord SAE
                </button>
            </div>
        </div>

        {{-- Widget 1 : Création de Programme --}}
        @if ($activeWidget === 'program_creator')
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs max-w-2xl space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Nouveau Programme d'Accompagnement</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Structure : {{ $this->activeTenant?->name }}</p>
                </div>

                <form wire:submit="createProgram" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nom du programme *</label>
                        <input
                            type="text"
                            wire:model="programName"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            placeholder="ex: AgriPulse — Accélération Agritech"
                        />
                        @error('programName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Description *</label>
                        <textarea
                            wire:model="programDescription"
                            required
                            rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            placeholder="Objectifs et contenu du programme..."
                        ></textarea>
                        @error('programDescription') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Durée</label>
                            <input
                                type="text"
                                wire:model="programDuration"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Seuil d'admission (/100)</label>
                            <input
                                type="number"
                                wire:model="programAdmissionThreshold"
                                min="0"
                                max="100"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    @if (count($this->levels) > 0)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Palier / Niveau visé</label>
                            <select
                                wire:model="programLevelId"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            >
                                <option value="">-- Tous niveaux --</option>
                                @foreach ($this->levels as $level)
                                    <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <input id="copilot_publish" type="checkbox" wire:model="programIsPublished" class="rounded border-slate-300" />
                        <label for="copilot_publish" class="text-xs text-slate-600 dark:text-slate-300">Publier immédiatement sur le portail public</label>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24276b] text-white text-xs font-bold transition cursor-pointer">
                            Créer le programme
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Widget 2 : Création de Cohorte --}}
        @if ($activeWidget === 'cohort_creator')
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs max-w-2xl space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Nouvelle Cohorte / Promotion</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Structure : {{ $this->activeTenant?->name }}</p>
                </div>

                @if (count($this->programs) === 0)
                    <p class="text-xs text-amber-600 dark:text-amber-400">Veuillez d'abord créer un programme avant de créer une cohorte.</p>
                @else
                    <form wire:submit="createCohort" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Programme parent *</label>
                            <select
                                wire:model="cohortProgramId"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                            >
                                @foreach ($this->programs as $program)
                                    <option value="{{ $program['id'] }}">{{ $program['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nom de la cohorte *</label>
                            <input
                                type="text"
                                wire:model="cohortName"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                                placeholder="ex: Promotion Automne 2026"
                            />
                            @error('cohortName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Capacité</label>
                                <input
                                    type="number"
                                    wire:model="cohortCapacity"
                                    min="1"
                                    max="500"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Statut initial</label>
                                <select
                                    wire:model="cohortStatus"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white"
                                >
                                    <option value="OUVERTE">Ouverte aux candidatures</option>
                                    <option value="EN_COURS">En cours d'incubation</option>
                                    <option value="CLOTUREE">Clôturée</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24276b] text-white text-xs font-bold transition cursor-pointer">
                                Créer la cohorte
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        {{-- Widget 3 : Tableau de bord Métriques --}}
        @if ($activeWidget === 'metrics_dashboard')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Programmes</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $this->metrics['programs'] }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Cohortes</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $this->metrics['cohorts'] }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Projets incubés</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $this->metrics['projects'] }}</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Candidatures</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $this->metrics['applications'] }}</div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
