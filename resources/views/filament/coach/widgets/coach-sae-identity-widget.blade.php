@php
    $sae = $this->getSae();
@endphp

@if($sae)
    <div class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-r from-white via-indigo-50/30 to-purple-50/20 p-5 shadow-xs dark:border-white/10 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950/20 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start sm:items-center gap-4">
                @if($avatar = \Filament\Facades\Filament::getTenantAvatarUrl($sae))
                    <img src="{{ $avatar }}" alt="{{ $sae->name }}" class="h-14 w-14 shrink-0 rounded-2xl border border-gray-200 object-contain p-1 shadow-xs dark:border-white/10">
                @else
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white shadow-xs">
                        {{ strtoupper(substr($sae->name, 0, 2)) }}
                    </div>
                @endif

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                            Structure d'accompagnement
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <h2 class="text-lg font-black text-gray-950 dark:text-white sm:text-xl">
                            {{ $sae->name }}
                        </h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Types de SAE :
                        </span>

                        @forelse($sae->types as $type)
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset shadow-xs @if($type->color === 'primary' || $type->color === 'teal') bg-teal-50 text-teal-800 ring-teal-600/20 dark:bg-teal-950/60 dark:text-teal-300 @elseif($type->color === 'success' || $type->color === 'emerald') bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-950/60 dark:text-emerald-300 @elseif($type->color === 'warning' || $type->color === 'amber') bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950/60 dark:text-amber-300 @elseif($type->color === 'info' || $type->color === 'sky') bg-sky-50 text-sky-800 ring-sky-600/20 dark:bg-sky-950/60 dark:text-sky-300 @elseif($type->color === 'indigo') bg-indigo-50 text-indigo-800 ring-indigo-600/20 dark:bg-indigo-950/60 dark:text-indigo-300 @elseif($type->color === 'purple') bg-purple-50 text-purple-800 ring-purple-600/20 dark:bg-purple-950/60 dark:text-purple-300 @elseif($type->color === 'rose' || $type->color === 'danger') bg-rose-50 text-rose-800 ring-rose-600/20 dark:bg-rose-950/60 dark:text-rose-300 @else bg-gray-100 text-gray-800 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-200 @endif">
                                <span class="h-1.5 w-1.5 rounded-full @if($type->color === 'primary' || $type->color === 'teal') bg-teal-500 @elseif($type->color === 'success' || $type->color === 'emerald') bg-emerald-500 @elseif($type->color === 'warning' || $type->color === 'amber') bg-amber-500 @elseif($type->color === 'info' || $type->color === 'sky') bg-sky-500 @elseif($type->color === 'indigo') bg-indigo-500 @elseif($type->color === 'purple') bg-purple-500 @elseif($type->color === 'rose' || $type->color === 'danger') bg-rose-500 @else bg-gray-500 @endif"></span>
                                {{ $type->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">
                                Non spécifié
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($sae->email || $sae->website)
                <div class="flex items-center gap-3 self-start sm:self-center text-xs text-gray-500 dark:text-gray-400">
                    @if($sae->website)
                        <a href="{{ $sae->website }}" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 flex items-center gap-1 font-medium transition">
                            <x-heroicon-m-globe-alt class="h-4 w-4" />
                            <span>Site web</span>
                        </a>
                    @endif
                    @if($sae->email)
                        <a href="mailto:{{ $sae->email }}" class="hover:text-indigo-600 flex items-center gap-1 font-medium transition">
                            <x-heroicon-m-envelope class="h-4 w-4" />
                            <span>{{ $sae->email }}</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
