    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bienvenido - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
             <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="antialiased h-screen flex overflow-hidden font-sans">
        <!-- Left Side: Logo Area -->
        <div class="hidden md:flex w-1/2 bg-cover bg-center items-center justify-center relative overflow-hidden" style="background-image: url('{{ asset('auth-bg.webp') }}');">
             <!-- Overlay -->
            <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>
            
            <div class="relative z-10 p-10">
                <img src="{{ asset('logo-final.png') }}" alt="Logo" class="max-w-md w-full h-auto object-contain drop-shadow-2xl">
            </div>
        </div>

        <!-- Right Side: Login/Register Area -->
        <div class="w-full md:w-1/2 bg-[#FDFDFC] dark:bg-[#0a0a0a] flex flex-col items-center justify-center p-8 relative">
            <!-- Mobile Logo (visible only on small screens) -->
            <div class="md:hidden mb-8">
                <img src="{{ asset('logo-final.png') }}" alt="Logo" class="h-16 w-auto">
            </div>

            <div class="w-full max-w-sm space-y-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-white">
                        Bienvenido
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Por favor, inicia sesión o regístrate para continuar.
                    </p>
                </div>

                <div class="mt-10 space-y-4">
                    @if (Route::has('login'))
                        <div class="space-y-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="flex w-full justify-center rounded-md bg-[#1b1b18] px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#3E3E3A] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:bg-white dark:text-black dark:hover:bg-gray-200">
                                    Ir al Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="flex w-full justify-center rounded-md bg-[#1b1b18] px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#3E3E3A] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:bg-white dark:text-black dark:hover:bg-gray-200 transition-colors duration-200">
                                    Iniciar Sesión
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="flex w-full justify-center rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm font-semibold text-[#1b1b18] shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 dark:bg-transparent dark:border-gray-600 dark:text-white dark:hover:bg-white/5 transition-colors duration-200">
                                        Registrarse
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
            
             <div class="absolute bottom-6 text-xs text-gray-400 dark:text-gray-600">
                &copy; 2026 sgiosci todos los derechos reservados
            </div>
        </div>
    </body>
</html>
