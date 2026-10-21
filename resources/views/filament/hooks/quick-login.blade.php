@php
    $currentPath = request()->path();
    $currentPanel = 'general';
    if (str_contains($currentPath, 'admin')) {
        $currentPanel = 'admin';
    } elseif (str_contains($currentPath, 'sae')) {
        $currentPanel = 'sae';
    } elseif (str_contains($currentPath, 'coach')) {
        $currentPanel = 'coach';
    } elseif (str_contains($currentPath, 'portal') || str_contains($currentPath, 'entrepreneur')) {
        $currentPanel = 'entrepreneur';
    }

    $accounts = \App\Http\Controllers\DevLoginController::getDemoAccounts();
@endphp

<div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800 text-xs">
    <div class="rounded-2xl p-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-[#F8B81D] animate-pulse"></span>
                Comptes de Test (Mode Démo)
            </span>
            <a href="{{ route('dev.comptes') }}" target="_blank" class="text-[11px] font-semibold text-[#2F3284] dark:text-[#F8B81D] hover:underline">
                Tous les comptes &rarr;
            </a>
        </div>

        <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Mot de passe universel : <code class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono font-bold text-slate-800 dark:text-slate-200">password</code>
        </p>

        {{-- Comptes recommandés pour ce panel --}}
        <div class="space-y-2">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Profils suggérés pour cet espace :
            </div>

            <div class="grid grid-cols-1 gap-2">
                @foreach($accounts as $key => $acc)
                    @if($currentPanel === 'general' || $acc['panel_id'] === $currentPanel)
                        <div class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-3 shadow-2xs">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 dark:text-white truncate text-xs">{{ $acc['name'] }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ $acc['badge'] }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate font-mono">
                                    {{ $acc['email'] }}
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    onclick="prefillLoginForm('{{ $acc['email'] }}', 'password')"
                                    title="Pré-remplir les champs"
                                    class="px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-[10px] font-semibold text-slate-700 dark:text-slate-300 transition"
                                >
                                    Remplir
                                </button>
                                <a
                                    href="{{ route('dev.login', ['as' => $key]) }}"
                                    title="Connexion instantanée"
                                    class="px-2.5 py-1 rounded-lg bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-[10px] transition shadow-xs flex items-center gap-1"
                                >
                                    <span>1-Clic</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Switch rapide vers les autres panels si besoin --}}
        <div class="pt-2 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center gap-1.5 text-[10px]">
            <span class="text-slate-400">Aller vers :</span>
            @if($currentPanel !== 'admin')
                <a href="{{ route('dev.login', ['as' => 'admin']) }}" class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 font-medium">Admin Central</a>
            @endif
            @if($currentPanel !== 'sae')
                <a href="{{ route('dev.login', ['as' => 'sae']) }}" class="px-2 py-0.5 rounded-md bg-teal-100 dark:bg-teal-900/40 text-teal-800 dark:text-teal-200 hover:bg-teal-200 font-medium">Structure SAE</a>
            @endif
            @if($currentPanel !== 'coach')
                <a href="{{ route('dev.login', ['as' => 'coach']) }}" class="px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-200 font-medium">Coach</a>
            @endif
            @if($currentPanel !== 'entrepreneur')
                <a href="{{ route('dev.login', ['as' => 'entrepreneur']) }}" class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 hover:bg-amber-200 font-medium">Entrepreneur</a>
            @endif
        </div>
    </div>
</div>

<script>
    function prefillLoginForm(email, password = 'password') {
        const emailInput = document.querySelector('input[type="email"]') ||
                           document.querySelector('input[name="data.email"]') ||
                           document.querySelector('input[id*="email"]') ||
                           document.querySelector('input[autocomplete="username"]');

        const passwordInput = document.querySelector('input[type="password"]') ||
                              document.querySelector('input[name="data.password"]') ||
                              document.querySelector('input[id*="password"]') ||
                              document.querySelector('input[autocomplete="current-password"]');

        if (emailInput) {
            emailInput.value = email;
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            emailInput.focus();
        }

        if (passwordInput) {
            passwordInput.value = password;
            passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
            passwordInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
</script>
