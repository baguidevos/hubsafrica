@if(session()->has('impersonator_admin_id'))
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-neutral-900 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 text-sm font-medium z-50 sticky top-0 shadow-lg border-b border-amber-600/40">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-black/20 text-neutral-900 font-bold text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </span>
            <span>
                <span class="font-bold uppercase tracking-wide text-xs bg-black/15 px-2 py-0.5 rounded mr-1">Mode Supervision Super Admin</span>
                Vous naviguez actuellement sur le portail de <strong>{{ auth()->user()?->name }}</strong> ({{ auth()->user()?->email }}).
            </span>
        </div>
        <div>
            <a href="{{ route('admin.impersonate.stop') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-neutral-950 text-amber-300 hover:bg-neutral-900 rounded-lg text-xs font-semibold uppercase tracking-wider transition-all shadow hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
                Quitter l'aperçu &amp; Revenir au panel Admin
            </a>
        </div>
    </div>
@endif
