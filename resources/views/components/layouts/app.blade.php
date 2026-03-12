<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'The Movie District' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen flex flex-col">
    <nav class="bg-gray-800 shadow-lg border-b border-gray-700 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <a href="/" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-orange-500">The Movie District</a>

            <div class="flex items-center space-x-6 w-full md:w-auto justify-center md:justify-end">
                <a href="/" class="hover:text-red-400 transition {{ request()->is('/') ? 'text-red-500 font-bold' : '' }}">Browse</a>
                <a href="/favorites" class="hover:text-red-400 transition {{ request()->is('favorites') ? 'text-red-500 font-bold' : '' }}">Favorites</a>
                <a href="/contact" class="hover:text-red-400 transition {{ request()->is('contact') ? 'text-red-500 font-bold' : '' }}">Contact</a>

                <livewire:search-dropdown />

                @auth
                    <div class="flex items-center space-x-3 border-l border-gray-600 pl-4">
                        <span class="text-sm text-gray-300 hidden lg:inline">{{ auth()->user()->name }}</span>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="/login" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <footer class="bg-gray-800 mt-12 py-8 text-center text-gray-400 border-t border-gray-700">
        <div class="container mx-auto px-4">
            <p>&copy; {{ date('Y') }} The Movie District. All rights reserved.</p>
            <div class="mt-4 flex justify-center space-x-4">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
            </div>
        </div>
    </footer>
</body>
</html>
