<style>
    /* 1. Ambiance de fond du Layout d'authentification */
    .fi-simple-layout {
        position: relative;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    html.dark .fi-simple-layout {
        background: 
            radial-gradient(circle at 10% 15%, rgba(47, 50, 132, 0.35) 0%, transparent 40%),
            radial-gradient(circle at 90% 85%, rgba(248, 184, 29, 0.15) 0%, transparent 45%),
            radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.8) 0%, transparent 60%),
            #070b19 !important;
    }

    html:not(.dark) .fi-simple-layout {
        background: 
            radial-gradient(circle at 5% 10%, rgba(47, 50, 132, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 95% 90%, rgba(248, 184, 29, 0.12) 0%, transparent 35%),
            #f8fafc !important;
    }

    /* 2. Carte centrale d'authentification Glassmorphism */
    .fi-simple-main-ctn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 2rem;
        padding-bottom: 2.5rem;
    }

    .fi-simple-main {
        border-radius: 1.5rem !important;
        padding: 2.25rem 2rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
    }

    @media (max-width: 640px) {
        .fi-simple-main {
            padding: 1.5rem 1.25rem !important;
            border-radius: 1.25rem !important;
            margin: 0 0.75rem !important;
        }
    }

    html.dark .fi-simple-main {
        background-color: rgba(15, 23, 42, 0.88) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.09) !important;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.7),
            0 0 0 1px rgba(248, 184, 29, 0.15),
            inset 0 1px 1px rgba(255, 255, 255, 0.08) !important;
    }

    html:not(.dark) .fi-simple-main {
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        box-shadow: 
            0 20px 40px -15px rgba(15, 23, 42, 0.08),
            0 0 0 1px rgba(248, 184, 29, 0.12),
            inset 0 1px 1px rgba(255, 255, 255, 0.8) !important;
    }

    /* 3. Bouton principal d'action */
    .fi-simple-main button[type="submit"] {
        border-radius: 0.875rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.02em !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .fi-simple-main button[type="submit"]:hover {
        transform: translateY(-1.5px);
        filter: brightness(1.05);
    }

    .fi-simple-main button[type="submit"]:active {
        transform: translateY(0);
    }

    /* 4. En-tête de logo et de titre */
    .fi-simple-header {
        margin-bottom: 1.5rem !important;
    }

    .fi-simple-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
    }

    /* 5. Liens d'action (mot de passe oublié, inscription) */
    .fi-simple-main a {
        transition: color 0.15s ease, text-decoration 0.15s ease;
    }
</style>
