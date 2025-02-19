<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScripts

</head>
<body class="w-full h-screen text-white bg-gray-900">
<main>
    <header class="py-10 md:py-28 flex flex-col items-center justify-center text-center">
        <div class="from-gray-200 via-black to-black bg-gradient-to-br flex items-center justify-center rounded-[0.55rem] select-none mt-16 md:mt-0" style="padding:1px">
            <figure class="shadow-white flex items-center justify-center w-32 h-32 p-3 bg-black rounded-lg shadow-2xl">
                <img src="https://pestphp.com/www/assets/logo.svg" alt="pestphp logo" class="ml-1">
            </figure>
        </div>
        <h1 class="sr-only">PESTPHP</h1>
        <h2 class="lg:text-8xl md:text-6xl drop-shadow-2xl md:mt-9 mt-6 text-3xl font-bold">
            Pest Plugin Browser<br />Playground
        </h2>
    </header>

    <x-test-pages-nav-menu />

    @yield('content')

    <footer class="md:px-0 p-12">
        <small class="text-white/75 md:text-xs block text-sm text-center">
            &copy; <span><script>document.write(new Date().getFullYear());</script></span>
            Pest
        </small>
    </footer>
</main>
</body>
</html>
