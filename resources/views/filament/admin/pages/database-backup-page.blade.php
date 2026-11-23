<x-filament-panels::page>
    @php
        $connectionInfo = $this->connectionInfo;
        $backups = $this->backups;
        $totalSize = $this->totalBackupSize;
    @endphp

    <div class="space-y-6">
        {{-- Cartes d'état de la base & du stockage --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Connexion active</div>
                <div class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
                    {{ strtoupper($connectionInfo['driver']) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $connectionInfo['database'] }} ({{ $connectionInfo['host'] }})
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sauvegardes stockées</div>
                <div class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
                    {{ count($backups) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Disque privé (storage/app/private/backups)
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Espace utilisé</div>
                <div class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
                    {{ $totalSize }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Non exposé publiquement
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 shadow-xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Dernier dump</div>
                <div class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
                    {{ $backups[0]['created_at'] ?? 'Aucun' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $backups[0]['size_human'] ?? 'Lancez votre première sauvegarde' }}
                </div>
            </div>
        </div>

        {{-- Panneau de génération de dump --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">
                        Générer un dump de production
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Exporte la structure et les données de la base active sans verrouiller les tables InnoDB (repli automatique PHP PDO si mysqldump/pg_dump est absent).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model.live="compress"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        >
                        Compresser en Gzip (.sql.gz)
                    </label>

                    <x-filament::button
                        type="button"
                        color="primary"
                        icon="heroicon-o-arrow-down-on-square-stack"
                        wire:click="createDump"
                        wire:loading.attr="disabled"
                        wire:target="createDump"
                    >
                        <span wire:loading.remove wire:target="createDump">Créer un dump maintenant</span>
                        <span wire:loading wire:target="createDump">Génération en cours...</span>
                    </x-filament::button>

                    @if(count($backups) > $retentionCount)
                        <x-filament::button
                            type="button"
                            color="gray"
                            icon="heroicon-o-trash"
                            wire:click="pruneBackups"
                            wire:confirm="Supprimer les sauvegardes les plus anciennes et ne conserver que les {{ $retentionCount }} plus récentes ?"
                        >
                            Nettoyer (garder {{ $retentionCount }})
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Liste des sauvegardes disponibles --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                    Archives disponibles
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Accès restreint aux Super Administrateurs
                </span>
            </div>

            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Fichier</th>
                                <th class="px-5 py-3 font-semibold">Format</th>
                                <th class="px-5 py-3 font-semibold">Taille</th>
                                <th class="px-5 py-3 font-semibold">Date de création</th>
                                <th class="px-5 py-3 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach($backups as $backup)
                                <tr wire:key="backup-{{ $backup['filename'] }}" class="hover:bg-gray-50/70 dark:hover:bg-white/5">
                                    <td class="px-5 py-3.5 font-mono font-semibold text-gray-900 dark:text-white">
                                        {{ $backup['filename'] }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span @class([
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold',
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' => $backup['compressed'],
                                            'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' => ! $backup['compressed'],
                                        ])>
                                            {{ $backup['compressed'] ? 'SQL GZIP' : 'SQL BRUT' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                                        {{ $backup['size_human'] }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                                        {{ $backup['created_at'] }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-filament::button
                                                tag="a"
                                                size="xs"
                                                color="info"
                                                icon="heroicon-o-arrow-down-tray"
                                                :href="route('admin.database-backups.download', ['filename' => $backup['filename']])"
                                            >
                                                Télécharger
                                            </x-filament::button>

                                            <x-filament::button
                                                type="button"
                                                size="xs"
                                                color="danger"
                                                icon="heroicon-o-trash"
                                                wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                                wire:confirm="Supprimer définitivement la sauvegarde « {{ $backup['filename'] }} » ?"
                                            >
                                                Supprimer
                                            </x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-10 text-center text-xs text-gray-500 dark:text-gray-400">
                    Aucune sauvegarde disponible pour le moment. Cliquez sur « Créer un dump maintenant » pour générer votre première archive.
                </div>
            @endif
        </div>

        {{-- Aide CLI pour l'automatisation en production --}}
        <div class="rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 p-4 text-xs text-gray-600 dark:text-gray-300 space-y-1.5">
            <div class="font-bold text-gray-900 dark:text-white">Utilisation en ligne de commande (SSH / Cron production)</div>
            <p class="font-mono text-[11px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-800 dark:text-gray-200">
                php artisan db:dump --keep=10
            </p>
        </div>
    </div>
</x-filament-panels::page>
