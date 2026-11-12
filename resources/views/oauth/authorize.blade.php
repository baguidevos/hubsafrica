<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autoriser la connexion IA — Hubs Africa</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            gold: '#F8B81D',
                            navy: '#2F3284',
                            dark: '#0F172A',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 text-slate-100 antialiased font-sans bg-radial from-slate-900 via-slate-950 to-black">
    <div class="w-full max-w-lg bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
        {{-- En-tête avec les icônes d'interconnexion --}}
        <div class="flex items-center justify-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#2F3284]/40 border border-[#2F3284] flex items-center justify-center shadow-lg">
                <span class="text-2xl font-black text-[#F8B81D]">HA</span>
            </div>
            <div class="flex items-center text-slate-500">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <div class="text-center space-y-2">
            <h1 class="text-xl font-extrabold text-white">Connexion de votre Assistant IA</h1>
            <p class="text-sm text-slate-400">
                L'application <strong class="text-amber-400">{{ $clientName }}</strong> demande l'autorisation d'accéder aux données de votre structure sur <strong>Hubs Africa</strong>.
            </p>
        </div>

        {{-- Formulaire d'approbation --}}
        <form method="POST" action="{{ route('oauth.approve') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="redirect_uri" value="{{ $redirectUri }}">
            <input type="hidden" name="client_id" value="{{ $client?->client_id }}">
            <input type="hidden" name="scope" value="{{ $scope }}">
            <input type="hidden" name="state" value="{{ $state }}">
            <input type="hidden" name="code_challenge" value="{{ $codeChallenge }}">
            <input type="hidden" name="code_challenge_method" value="{{ $codeChallengeMethod }}">

            {{-- Sélection de la SAE --}}
            <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    Structure d'Accompagnement (SAE) à connecter
                </label>
                @if($userSaes->count() > 1)
                    <select name="sae_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                        @foreach($userSaes as $sae)
                            <option value="{{ $sae->id }}" @selected($sae->id === $selectedSaeId)>
                                {{ $sae->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="hidden" name="sae_id" value="{{ $userSaes->first()->id }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
                            {{ substr($userSaes->first()->name, 0, 2) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">{{ $userSaes->first()->name }}</div>
                            <div class="text-xs text-slate-400">Connecté en tant que {{ $user->name }} ({{ $user->email }})</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Liste des permissions --}}
            <div class="space-y-3">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Permissions accordées à l'IA</div>
                <div class="space-y-2 text-xs text-slate-300">
                    <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800/80">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Consulter les métriques, indicateurs d'impact et statistiques de votre structure</span>
                    </div>
                    <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800/80">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Rechercher et évaluer les dossiers de candidatures de vos programmes</span>
                    </div>
                    <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800/80">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Piloter les cohortes, projets incubés et enregistrer les sessions de coaching</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 pt-2">
                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition cursor-pointer"
                >
                    Autoriser l'accès
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('oauth.deny') }}" class="text-center">
            @csrf
            <input type="hidden" name="redirect_uri" value="{{ $redirectUri }}">
            <input type="hidden" name="state" value="{{ $state }}">
            <button
                type="submit"
                class="text-xs text-slate-400 hover:text-red-400 transition underline underline-offset-4 cursor-pointer"
            >
                Refuser et annuler
            </button>
        </form>
    </div>
</body>
</html>
