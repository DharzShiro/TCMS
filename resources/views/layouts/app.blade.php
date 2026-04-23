<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'TCMS'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <script>
            // Prevent flash of unstyled content
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }

            document.addEventListener('alpine:init', () => {
                // Sidebar store
                Alpine.store('sidebar', {
                    isOpen: window.innerWidth >= 1024,
                    toggle() {
                        this.isOpen = !this.isOpen;
                    }
                });

                // Dark mode store
                Alpine.store('darkMode', {
                    init() {
                        const theme = localStorage.getItem('theme');
                        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                        this.on = theme === 'dark' || (!theme && prefersDark);

                        if (this.on) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    },

                    on: false,

                    toggle() {
                        this.on = !this.on;
                        localStorage.setItem('theme', this.on ? 'dark' : 'light');

                        if (this.on) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                });
            });
        </script>

        <style>
            [x-cloak] { display: none !important; }

            /* Ensure proper box-sizing */
            *, *::before, *::after {
                box-sizing: border-box;
            }

            /* Prevent horizontal overflow */
            html, body {
                overflow-x: hidden;
                max-width: 100vw;
            }

            /* Responsive font sizes */
            @media (max-width: 640px) {
                html { font-size: 14px; }
            }

            @media (min-width: 641px) and (max-width: 768px) {
                html { font-size: 15px; }
            }

            @media (min-width: 769px) {
                html { font-size: 16px; }
            }
        </style>

        @stack('styles')
    </head>
    <body class="font-sans antialiased"
          :class="{ 'dark bg-[#111827]': $store.darkMode.on, 'bg-gray-50': !$store.darkMode.on }">
        <div class="min-h-screen flex flex-col w-full">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Page Wrapper -->
            <div class="flex flex-1 relative">
                <!-- Sidebar -->
                @include('layouts.sidebar')

                <!-- Main Content -->
                <div class="flex-1 pt-16 transition-all duration-300 ease-in-out w-full"
                     :class="{
                         'lg:pl-72': $store.sidebar.isOpen,
                         'pl-0': !$store.sidebar.isOpen
                     }">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white dark:bg-gray-800 shadow">
                            <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="py-4 sm:py-6 lg:py-8 px-4 sm:px-6 lg:px-8 w-full">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot ?? '' }}
                        @endif
                    </main>
                </div>
            </div>
        </div>

        @stack('modals')
        @stack('scripts')
    </body>
</html>