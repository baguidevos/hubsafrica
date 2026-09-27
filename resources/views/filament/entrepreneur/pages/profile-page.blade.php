<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">Informations d'identité</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Prénom</label>
                    <input type="text" wire:model="firstName" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @error('firstName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Nom</label>
                    <input type="text" wire:model="lastName" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @error('lastName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Email</label>
                    <input type="email" wire:model="email" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Téléphone</label>
                    <input type="text" wire:model="phone" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                    @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Genre</label>
                    <select wire:model="gender" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                        <option value="">Sélectionner un genre</option>
                        <option value="M">Homme</option>
                        <option value="F">Femme</option>
                        <option value="AUTRE">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Date de naissance</label>
                    <input type="date" wire:model="birthDate" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">Localisation & Situation professionnelle</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Pays</label>
                    <input type="text" wire:model="country" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Territoire / Région</label>
                    <input type="text" wire:model="territory" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Niveau d'études</label>
                    <input type="text" wire:model="educationLevel" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Profession</label>
                    <input type="text" wire:model="profession" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Statut professionnel</label>
                    <input type="text" wire:model="professionalStatus" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-x-3">
            <x-filament::button type="submit" color="primary">
                Enregistrer les modifications
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
