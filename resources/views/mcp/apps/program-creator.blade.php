<x-mcp::app :title="$title">
    <x-slot:head>
        <script type="module">
            window.mcpAppReady = false;
            window.mcpAppInstance = null;

            createMcpApp(async (app) => {
                window.mcpAppInstance = app;
                window.mcpAppReady = true;
                window.dispatchEvent(new CustomEvent('mcp-app-ready'));
            });
        </script>
    </x-slot:head>

    <div
        x-data="programCreatorForm()"
        class="p-5 bg-white text-slate-900 font-sans antialiased max-w-xl mx-auto"
    >
        <div class="border border-slate-200 rounded-2xl p-5 space-y-5 shadow-xs">
            {{-- En-tête --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Nouveau Programme d'Accompagnement</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Structure : <span class="font-semibold text-slate-700">{{ $saeName }}</span></p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                    Hubs Africa
                </span>
            </div>

            {{-- Message de succès --}}
            <template x-if="createdProgram">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                    <div class="text-sm font-bold" x-text="'Programme créé : ' + createdProgram.name"></div>
                    <p class="text-xs text-emerald-700" x-text="'ID #' + createdProgram.id + ' — Durée : ' + createdProgram.duration"></p>
                    <button
                        type="button"
                        @click="resetForm()"
                        class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition"
                    >
                        Créer un autre programme
                    </button>
                </div>
            </template>

            {{-- Message d'erreur --}}
            <template x-if="errorMessage">
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium" x-text="errorMessage"></div>
            </template>

            {{-- Formulaire --}}
            <form x-show="!createdProgram" @submit.prevent="submit()" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nom du programme <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.name"
                        required
                        maxlength="200"
                        placeholder="ex: AgriPulse — Accélération Agritech 2026"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        x-model="form.description"
                        required
                        rows="3"
                        placeholder="Décrivez les objectifs et la proposition de valeur du programme..."
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durée</label>
                        <input
                            type="text"
                            x-model="form.duration"
                            placeholder="ex: 6 mois"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Seuil d'admission (/100)</label>
                        <input
                            type="number"
                            x-model.number="form.admission_threshold"
                            min="0"
                            max="100"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                        />
                    </div>
                </div>

                @if(count($levels) > 0)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Niveau / Palier visé</label>
                        <select
                            x-model="form.level_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                        >
                            <option value="">-- Tous niveaux --</option>
                            @foreach($levels as $level)
                                <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2 pt-1">
                    <input
                        id="is_published"
                        type="checkbox"
                        x-model="form.is_published"
                        class="w-4 h-4 rounded border-slate-300 text-[#2F3284]"
                    />
                    <label for="is_published" class="text-xs text-slate-700 font-medium">
                        Publier immédiatement sur le portail public
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24276b] disabled:opacity-50 text-white text-xs font-bold transition cursor-pointer"
                    >
                        <span x-show="!isSubmitting">Créer le programme</span>
                        <span x-show="isSubmitting">Création en cours...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function programCreatorForm() {
            return {
                isSubmitting: false,
                errorMessage: null,
                createdProgram: null,
                form: {
                    name: '',
                    description: '',
                    duration: '6 mois',
                    admission_threshold: 60,
                    level_id: '',
                    is_published: false,
                },

                resetForm() {
                    this.createdProgram = null;
                    this.errorMessage = null;
                    this.form = {
                        name: '',
                        description: '',
                        duration: '6 mois',
                        admission_threshold: 60,
                        level_id: '',
                        is_published: false,
                    };
                },

                async submit() {
                    if (!window.mcpAppInstance) {
                        this.errorMessage = "Connexion au client MCP non initialisée.";
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = null;

                    const payload = {
                        name: this.form.name.trim(),
                        description: this.form.description.trim(),
                        duration: this.form.duration.trim() || '6 mois',
                        admission_threshold: Number(this.form.admission_threshold) || 60,
                        is_published: Boolean(this.form.is_published),
                    };

                    if (this.form.level_id) {
                        payload.level_id = Number(this.form.level_id);
                    }

                    try {
                        const result = await window.mcpAppInstance.callServerTool('create_program', payload);

                        if (result?.isError) {
                            this.errorMessage = result.content?.[0]?.text || "Impossible de créer le programme.";
                            return;
                        }

                        const structured = result?.structuredContent || JSON.parse(result?.content?.[0]?.text || '{}');
                        this.createdProgram = structured.program || {
                            id: '—',
                            name: payload.name,
                            duration: payload.duration,
                        };
                    } catch (err) {
                        this.errorMessage = "Une erreur est survenue lors de la création du programme.";
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</x-mcp::app>
