<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur d'autorisation — Hubs Africa</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 text-slate-100 antialiased font-sans bg-slate-950">
    <div class="w-full max-w-md bg-slate-900 border border-red-500/30 rounded-3xl p-8 shadow-2xl text-center space-y-5">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-red-500/10 text-red-400 border border-red-500/30 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-lg font-bold text-white">Impossible d'établir la connexion</h1>
        <p class="text-sm text-slate-400 leading-relaxed">{{ $errorMessage }}</p>
        <div class="pt-2">
            <a href="{{ route('home') }}" class="inline-block px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition">
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
