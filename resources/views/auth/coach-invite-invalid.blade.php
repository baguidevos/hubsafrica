<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Invalide · Hub Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-slate-800 antialiased">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 p-8 space-y-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 mb-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Lien d'invitation expiré ou invalide</h1>
        <p class="text-sm text-slate-500">
            {{ $message ?? 'Ce lien d\'invitation a déjà été utilisé ou n\'est plus valide. Veuillez contacter le responsable de la structure d\'accompagnement pour obtenir un nouveau lien.' }}
        </p>
        <div class="pt-4">
            <a href="{{ url('/') }}"
                class="inline-flex items-center px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs rounded-xl transition">
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
