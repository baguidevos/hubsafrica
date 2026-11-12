<x-mcp::app :title="$title">
    <x-slot:head>
        <script type="module">
            window.mcpAppInstance = null;

            createMcpApp(async (app) => {
                window.mcpAppInstance = app;
            });
        </script>
    </x-slot:head>

    <div
        x-data="cohortCreatorForm()"
        class="p-5 bg-white text-slate-900 font-sans antialiased max-w-xl mx-auto"
    >
        <div class="border border-slate-200 rounded-2xl p-5 space-y-5 shadow-xs">
            {{-- En-tête --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Nouvelle Cohorte / Promotion</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Structure : <span class="font-semibold text-slate-700">{{ $saeName }}</span></p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                    Hubs Africa
                </span>
            </div>

            {{-- Succès --}}
            <template x-if="createdCohort">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                    <div class="text-sm font-bold" x-text="'Cohorte créée : ' + createdCohort.name"></div>
                    <p class="text-xs text-emerald-700" x-text="'ID #' + createdCohort.id + ' — Statut : ' + createdCohort.status + ' — Capacité : ' + createdCohort.capacity"></p>
                    <button
                        type="button"
                        @click="resetForm()"
                        class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition"
                    >
                        Créer une autre cohorte
                    </button>
                </div>
            </template>

            {{-- Erreur --}}
            <template x-if="errorMessage">
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium" x-text="errorMessage"></div>
            </template>

            @if(count($programs) === 0)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    Aucun programme disponible dans cette SAE. Créez d'abord un programme avant de créer une cohorte.
                </div>
            @else
                <form x-show="!createdCohort" @submit.prevent="submit()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Programme parent <span class="text-red-500">*</span>
                        </label>
                        <select
                            x-model="form.program_id"
                            required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                        >
                            <option value="">-- Sélectionnez un programme --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program['id'] }}">{{ $program['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nom de la cohorte <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            maxlength="150"
                            placeholder="ex: Promotion Printemps 2026"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Capacité (places)</label>
                            <input
                                type="number"
                                x-model.number="form.capacity"
                                min="1"
                                max="500"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Statut initial</label>
                            <select
                                x-model="form.status"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                            >
                                <option value="OUVERTE">Ouverte aux candidatures</option>
                                <option value="EN_COURS">En cours d'incubation</option>
                                <option value="CLOTUREE">Clôturée</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Date de démarrage</label>
                            <input
                                type="date"
                                x-model="form.start_date"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Date de fin</label>
                            <input
                                type="date"
                                x-model="form.end_date"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                            />
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24276b] disabled:opacity-50 text-white text-xs font-bold transition cursor-pointer"
                        >
                            <span x-show="!isSubmitting">Créer la cohorte</span>
                            <span x-show="isSubmitting">Création en cours...</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function cohortCreatorForm() {
            return {
                isSubmitting: false,
                errorMessage: null,
                createdCohort: null,
                form: {
                    program_id: '{{ $programs[0]['id'] ?? '' }}',
                    name: '',
                    capacity: 20,
                    status: 'OUVERTE',
                    start_date: '',
                    end_date: '',
                },

                resetForm() {
                    this.createdCohort = null;
                    this.errorMessage = null;
                    this.form.name = '';
                },

                async submit() {
                    if (!window.mcpAppInstance) {
                        this.errorMessage = "Connexion au client MCP non initialisée.";
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = null;

                    const payload = {
                        program_id: Number(this.form.program_id),
                        name: this.form.name.trim(),
                        capacity: Number(this.form.capacity) || 20,
                        status: this.form.status,
                    };

                    if (this.form.start_date) {
                        payload.start_date = this.form.start_date;
                    }
                    if (this.form.end_date) {
                        payload.end_date = this.form.end_date;
                    }

                    try {
                        const result = await window.mcpAppInstance.callServerTool('create_cohort', payload);

                        if (result?.isError) {
                            this.errorMessage = result.content?.[0]?.text || "Impossible de créer la cohorte.";
                            return;
                        }

                        const structured = result?.structuredContent || JSON.parse(result?.content?.[0]?.text || '{}');
                        this.createdCohort = structured.cohort || {
                            id: '—',
                            name: payload.name,
                            status: payload.status,
                            capacity: payload.capacity,
                        };
                    } catch (err) {
                        this.errorMessage = "Une erreur est survenue lors de la création de la cohorte.";
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</x-mcp::app>
