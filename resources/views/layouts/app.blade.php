<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ER Drill Management') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900" style="font-family: Manrope, sans-serif; background: white;">

        {{-- Background decoration --}}
        <div style="pointer-events: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -10; overflow: hidden;">
            <div style="position: absolute; top: -180px; right: -180px; width: 640px; height: 640px; border-radius: 9999px; background: radial-gradient(circle, rgba(43,45,138,0.06) 0%, transparent 70%);"></div>
            <div style="position: absolute; bottom: -200px; left: -200px; width: 560px; height: 560px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.05) 0%, transparent 70%);"></div>
            <div style="position: absolute; inset: 0; background: linear-gradient(175deg, #F0F3FA 0%, #FFFFFF 55%);"></div>
        </div>

        <div style="min-height: 100vh;">
            <livewire:layout.navigation />
            <livewire:profile.name-confirmation-modal />

            @if (isset($header))
                <header style="border-bottom: 1px solid rgba(43,45,138,0.08); background: rgba(255,255,255,0.7); backdrop-filter: blur(8px);">
                    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem;">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main style="max-width: 80rem; margin: 0 auto; padding: 2rem 1.5rem;">
                @if (session('status'))
                    <div style="margin-bottom: 1.5rem; border-radius: 1rem; border: 1px solid rgba(0,163,200,0.3); background: rgba(0,163,200,0.08); padding: 0.875rem 1rem; font-size: 0.875rem; font-weight: 500; color: #0a6f87;">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
