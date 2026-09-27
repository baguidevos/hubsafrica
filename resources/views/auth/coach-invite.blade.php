<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Coach · Hub Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800 antialiased">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Rejoindre en tant que Coach</h1>
            <p class="text-sm text-slate-500">
                Vous avez été invité(e) par la structure <span class="font-semibold text-teal-700">{{ $sae->name }}</span> à accompagner des entrepreneurs.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-200">
                <ul class="text-xs text-red-700 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('coach.invite.register', $invitation->token) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email (réservé)</label>
                <input type="email" value="{{ $invitation->email }}" disabled
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-sm cursor-not-allowed">
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nom complet</label>
                <input type="text" id="name" name="name" value="{{ old('name', $invitation->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 text-sm transition outline-none"
                    placeholder="Ex: Dr. Kouamé Koffi">
            </div>

            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Numéro de téléphone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 text-sm transition outline-none"
                    placeholder="+225 07 00 00 00">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Créer un mot de passe</label>
                <input type="password" id="password" name="password" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 text-sm transition outline-none"
                    placeholder="Minimum 8 caractères">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 text-sm transition outline-none"
                    placeholder="Répétez votre mot de passe">
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-medium text-sm rounded-xl shadow-sm hover:shadow transition duration-150 flex items-center justify-center space-x-2">
                <span>Créer mon compte Coach</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <p class="text-center text-xs text-slate-400">
            Plateforme Hub Africa · Accompagnement Entrepreneurial Panafricain
        </p>
    </div>
</body>
</html>
