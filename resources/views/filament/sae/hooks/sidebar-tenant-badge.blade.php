@php
    $tenant = \Filament\Facades\Filament::getTenant();
@endphp

@if($tenant)
    <div class="px-3 py-2.5 mx-2 my-2 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 flex items-center gap-3">
        @if($avatar = \Filament\Facades\Filament::getTenantAvatarUrl($tenant))
            <img src="{{ $avatar }}" alt="{{ $tenant->name }}" class="w-8 h-8 rounded-lg object-contain bg-white dark:bg-white/10 border border-gray-200/70 dark:border-white/10 p-0.5 shrink-0 shadow-xs">
        @else
            <div class="w-8 h-8 rounded-lg bg-teal-600 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                {{ strtoupper(substr($tenant->name, 0, 2)) }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <span class="block text-[10px] text-gray-400 dark:text-gray-400 uppercase tracking-wider font-semibold leading-none">Structure SAE</span>
            <span class="block text-xs font-bold text-gray-950 dark:text-white truncate mt-0.5" title="{{ $tenant->name }}">{{ $tenant->name }}</span>
        </div>
    </div>
@endif
