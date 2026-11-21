@if (filament()->isSidebarCollapsibleOnDesktop())
    <style>
        @media (min-width: 1024px) {
            .fi-topbar-collapse-sidebar-btn-ctn {
                display: none !important;
            }

            #fi-main-sidebar {
                overflow: visible !important;
            }

            .hubs-sidebar-floating-toggle {
                position: absolute;
                top: 1.15rem;
                right: -0.85rem;
                z-index: 40;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.75rem;
                height: 1.75rem;
                border-radius: 9999px;
                border: 1px solid rgb(226 232 240);
                background-color: #ffffff;
                color: rgb(71 85 105);
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
                cursor: pointer;
                transition: transform 150ms ease, background-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
            }

            .hubs-sidebar-floating-toggle:hover {
                background-color: rgb(248 250 252);
                color: rgb(15 23 42);
                transform: scale(1.08);
                box-shadow: 0 4px 10px rgba(15, 23, 42, 0.16);
            }

            .dark .hubs-sidebar-floating-toggle {
                border-color: rgba(255, 255, 255, 0.14);
                background-color: rgb(24 24 27);
                color: rgb(212 212 216);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
            }

            .dark .hubs-sidebar-floating-toggle:hover {
                background-color: rgb(39 39 42);
                color: #ffffff;
            }
        }

        @media (max-width: 1023px) {
            .hubs-sidebar-floating-toggle {
                display: none !important;
            }
        }
    </style>

    <button
        type="button"
        x-data="{}"
        x-cloak
        aria-controls="fi-main-sidebar"
        x-bind:aria-expanded="$store.sidebar.isOpen"
        x-bind:title="$store.sidebar.isOpen ? 'Replier le menu' : 'Déplier le menu'"
        x-bind:aria-label="$store.sidebar.isOpen ? 'Replier le menu' : 'Déplier le menu'"
        x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
        class="hubs-sidebar-floating-toggle"
    >
        <svg
            class="h-3.5 w-3.5 transition-transform duration-200"
            x-bind:class="$store.sidebar.isOpen ? '' : 'rotate-180'"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
        </svg>
    </button>
@endif
