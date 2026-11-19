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
        x-data="formCreatorApp()"
        class="p-5 bg-white text-slate-900 font-sans antialiased max-w-2xl mx-auto"
    >
        <div class="border border-slate-200 rounded-2xl p-5 space-y-5 shadow-xs">
            {{-- En-tête --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Créer un Formulaire / Questionnaire</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Structure : <span class="font-semibold text-slate-700">{{ $saeName }}</span></p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                    Hubs Africa
                </span>
            </div>

            {{-- Message de succès --}}
            <template x-if="createdForm">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-2">
                    <div class="text-sm font-bold" x-text="'Formulaire créé : ' + createdForm.name"></div>
                    <p class="text-xs text-emerald-700" x-text="'ID #' + createdForm.id + ' — ' + createdForm.type_label + ' (' + createdForm.fields_count + ' question(s))'"></p>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <a
                            x-show="createdForm.public_url"
                            :href="createdForm.public_url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#2F3284] text-white text-xs font-semibold hover:bg-[#24276b] transition"
                        >
                            Ouvrir le formulaire
                        </a>
                        <button
                            type="button"
                            @click="resetForm()"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition cursor-pointer"
                        >
                            Créer un autre formulaire
                        </button>
                    </div>
                </div>
            </template>

            {{-- Message d'erreur --}}
            <template x-if="errorMessage">
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium" x-text="errorMessage"></div>
            </template>

            {{-- Formulaire --}}
            <form x-show="!createdForm" @submit.prevent="submit()" class="space-y-4">
                {{-- Informations générales --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Titre du formulaire <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            maxlength="255"
                            placeholder="ex: Diagnostic initial Cohorte 2026"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Type de formulaire <span class="text-red-500">*</span>
                        </label>
                        <select
                            x-model="form.type"
                            required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                        >
                            @foreach($formTypes as $formType)
                                <option value="{{ $formType['value'] }}">{{ $formType['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea
                        x-model="form.description"
                        rows="2"
                        placeholder="Objectif du formulaire et consignes pour les répondants..."
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-[#2F3284]"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if(count($programs) > 0)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Programme associé (optionnel)</label>
                            <select
                                x-model="form.program_id"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                            >
                                <option value="">-- Aucun programme spécifique --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program['id'] }}">{{ $program['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(count($levels) > 0)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Palier cible (optionnel)</label>
                            <select
                                x-model="form.target_level_id"
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:border-[#2F3284]"
                            >
                                <option value="">-- Tous paliers --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Constructeur de questions --}}
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Questions du formulaire <span class="text-red-500">*</span>
                            </h3>
                            <p class="text-[11px] text-slate-500">Ajoutez les champs que les répondants devront remplir.</p>
                        </div>
                        <button
                            type="button"
                            @click="addField()"
                            class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition cursor-pointer"
                        >
                            + Ajouter une question
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(field, index) in form.fields" :key="field.uid">
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                                    <div class="sm:col-span-6">
                                        <input
                                            type="text"
                                            x-model="field.label"
                                            required
                                            placeholder="Libellé de la question (ex: Chiffre d'affaires mensuel)"
                                            class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#2F3284]"
                                        />
                                    </div>
                                    <div class="sm:col-span-4">
                                        <select
                                            x-model="field.type"
                                            class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#2F3284]"
                                        >
                                            <option value="text">Texte court</option>
                                            <option value="textarea">Texte long</option>
                                            <option value="email">Adresse email</option>
                                            <option value="phone">Téléphone</option>
                                            <option value="number">Nombre</option>
                                            <option value="select">Liste déroulante</option>
                                            <option value="radio">Choix unique</option>
                                            <option value="checkboxes">Cases à cocher</option>
                                            <option value="checkbox">Case unique (Oui/Non)</option>
                                            <option value="date">Date</option>
                                            <option value="file">Fichier</option>
                                            <option value="rating">Note</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                        <label class="inline-flex items-center gap-1 text-[11px] text-slate-600 cursor-pointer">
                                            <input type="checkbox" x-model="field.required" class="rounded border-slate-300 text-[#2F3284]" />
                                            <span>Requis</span>
                                        </label>
                                        <button
                                            type="button"
                                            @click="removeField(index)"
                                            x-show="form.fields.length > 1"
                                            title="Supprimer cette question"
                                            class="text-xs font-bold text-red-500 hover:text-red-700 px-1.5 py-0.5 rounded cursor-pointer"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>

                                <template x-if="['select', 'radio', 'checkboxes'].includes(field.type)">
                                    <div>
                                        <input
                                            type="text"
                                            x-model="field.optionsText"
                                            placeholder="Options séparées par des virgules (ex: Idéation, MVP, Croissance)"
                                            class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#2F3284]"
                                        />
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Options de publication --}}
                <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-slate-100">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 font-medium cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="w-4 h-4 rounded border-slate-300 text-[#2F3284]" />
                        <span>Activer immédiatement</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 font-medium cursor-pointer">
                        <input type="checkbox" x-model="form.is_mandatory" class="w-4 h-4 rounded border-slate-300 text-[#2F3284]" />
                        <span>Obligatoire</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 font-medium cursor-pointer">
                        <input type="checkbox" x-model="form.wizard_mode" class="w-4 h-4 rounded border-slate-300 text-[#2F3284]" />
                        <span>Mode étapes (Wizard)</span>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24276b] disabled:opacity-50 text-white text-xs font-bold transition cursor-pointer"
                    >
                        <span x-show="!isSubmitting">Créer le formulaire</span>
                        <span x-show="isSubmitting">Création en cours...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formCreatorApp() {
            let nextUid = 1;
            const makeField = (label = '', type = 'text', required = true) => ({
                uid: nextUid++,
                label,
                type,
                required,
                optionsText: '',
            });

            return {
                isSubmitting: false,
                errorMessage: null,
                createdForm: null,
                form: {
                    name: '',
                    type: 'DIAGNOSTIC',
                    description: '',
                    program_id: '',
                    target_level_id: '',
                    is_mandatory: true,
                    is_active: true,
                    wizard_mode: false,
                    fields: [
                        makeField('Nom du projet ou de la structure', 'text', true),
                        makeField('Commentaires ou besoins prioritaires', 'textarea', false),
                    ],
                },

                addField() {
                    this.form.fields.push(makeField('', 'text', false));
                },

                removeField(index) {
                    if (this.form.fields.length <= 1) {
                        return;
                    }
                    this.form.fields.splice(index, 1);
                },

                resetForm() {
                    this.createdForm = null;
                    this.errorMessage = null;
                    this.form.name = '';
                    this.form.description = '';
                    this.form.program_id = '';
                    this.form.target_level_id = '';
                    this.form.fields = [
                        makeField('Nom du projet ou de la structure', 'text', true),
                        makeField('Commentaires ou besoins prioritaires', 'textarea', false),
                    ];
                },

                async submit() {
                    if (!window.mcpAppInstance) {
                        this.errorMessage = "Connexion au client MCP non initialisée.";
                        return;
                    }

                    const normalizedFields = this.form.fields
                        .filter(field => field.label.trim() !== '')
                        .map(field => {
                            const entry = {
                                label: field.label.trim(),
                                type: field.type,
                                required: Boolean(field.required),
                            };

                            if (['select', 'radio', 'checkboxes'].includes(field.type) && field.optionsText.trim() !== '') {
                                entry.options = field.optionsText
                                    .split(',')
                                    .map(opt => opt.trim())
                                    .filter(Boolean);
                            }

                            return entry;
                        });

                    if (normalizedFields.length === 0) {
                        this.errorMessage = "Veuillez ajouter au moins une question avec un libellé.";
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = null;

                    const payload = {
                        name: this.form.name.trim(),
                        type: this.form.type,
                        description: this.form.description.trim() || null,
                        is_mandatory: Boolean(this.form.is_mandatory),
                        is_active: Boolean(this.form.is_active),
                        wizard_mode: Boolean(this.form.wizard_mode),
                        fields: normalizedFields,
                    };

                    if (this.form.program_id) {
                        payload.program_id = Number(this.form.program_id);
                    }

                    if (this.form.target_level_id) {
                        payload.target_level_id = Number(this.form.target_level_id);
                    }

                    try {
                        const result = await window.mcpAppInstance.callServerTool('create_form', payload);

                        if (result?.isError) {
                            this.errorMessage = result.content?.[0]?.text || "Impossible de créer le formulaire.";
                            return;
                        }

                        const structured = result?.structuredContent || JSON.parse(result?.content?.[0]?.text || '{}');
                        this.createdForm = structured.form || {
                            id: '—',
                            name: payload.name,
                            type_label: payload.type,
                            fields_count: normalizedFields.length,
                            public_url: null,
                        };
                    } catch (err) {
                        this.errorMessage = "Une erreur est survenue lors de la création du formulaire.";
                    } finally {
                        this.isSubmitting = false;
                    }
                },
            };
        }
    </script>
</x-mcp::app>
