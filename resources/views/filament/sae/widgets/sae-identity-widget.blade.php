@php
    $sae = $this->getSae();
@endphp

@if($sae)
    <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-r from-white via-stone-50/50 to-teal-50/30 p-5 shadow-xs dark:border-white/10 dark:from-gray-900 dark:via-gray-900 dark:to-teal-950/20 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start sm:items-center gap-4">
                @if($avatar = \Filament\Facades\Filament::getTenantAvatarUrl($sae))
                    <img src="{{ $avatar }}" alt="{{ $sae->name }}" class="h-14 w-14 shrink-0 rounded-2xl border border-gray-200 object-contain p-1 shadow-xs dark:border-white/10">
                @else
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-teal-600 text-lg font-black text-white shadow-xs">
                        {{ strtoupper(substr($sae->name, 0, 2)) }}
                    </div>
                @endif

                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-black text-gray-950 dark:text-white sm:text-xl">
                            {{ $sae->name }}
                        </h2>
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/60 dark:text-emerald-300">
                            {{ $sae->status ?? 'ACTIVE' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Types de structure :
                        </span>

                        @forelse($sae->types as $type)
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset shadow-xs
                                @if($type->color === 'primary' || $type->color === 'teal')
                                    bg-teal-50 text-teal-800 ring-teal-600/20 dark:bg-teal-950/60 dark:text-teal-300
                                @elseif($type->color === 'success' || $type->color === 'emerald')
                                    bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-950/60 dark:text-emerald-300
                                @elseif($type->color === 'warning' || $type->color === 'amber')
                                    bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950/60 dark:text-amber-300
                                @elseif($type->color === 'info' || $type->color === 'sky')
                                    bg-sky-50 text-sky-800 ring-sky-600/20 dark:bg-sky-950/60 dark:text-sky-300
                                @elseif($type->color === 'indigo')
                                    bg-indigo-50 text-indigo-800 ring-indigo-600/20 dark:bg-indigo-950/60 dark:text-indigo-300
                                @elseif($type->color === 'purple')
                                    bg-purple-50 text-purple-800 ring-purple-600/20 dark:bg-purple-950/60 dark:text-purple-300
                                @elseif($type->color === 'rose' || $type->color === 'danger')
                                    bg-rose-50 text-rose-800 ring-rose-600/20 dark:bg-rose-950/60 dark:text-rose-300
                                @else
                                    bg-gray-100 text-gray-800 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-200
                                @endif
                            ">
                                <span class="h-1.5 w-1.5 rounded-full
                                    @if($type->color === 'primary' || $type->color === 'teal') bg-teal-500
                                    @elseif($type->color === 'success' || $type->color === 'emerald') bg-emerald-500
                                    @elseif($type->color === 'warning' || $type->color === 'amber') bg-amber-500
                                    @elseif($type->color === 'info' || $type->color === 'sky') bg-sky-500
                                    @elseif($type->color === 'indigo') bg-indigo-500
                                    @elseif($type->color === 'purple') bg-purple-500
                                    @elseif($type->color === 'rose' || $type->color === 'danger') bg-rose-500
                                    @else bg-gray-500
                                    @endif
                                "></span>
                                {{ $type->name }}
                            </span>
                        @empty
                            <a href="{{ route('filament.sae.tenant.profile', ['tenant' => $sae]) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-dashed border-amber-300 bg-amber-50/80 px-2.5 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-700/50 dark:bg-amber-950/30 dark:text-amber-300">
                                <x-heroicon-m-plus class="h-3.5 w-3.5" />
                                Définir les types de la SAE (Couveuse, Coworking, Pépinière...)
                            </a>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <a href="{{ route('filament.sae.tenant.profile', ['tenant' => $sae]) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 hover:text-gray-900 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                    <x-heroicon-m-adjustments-horizontal class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                    <span>Modifier le profil & types</span>
                </a>
            </div>
        </div>
    </div>
@endif
