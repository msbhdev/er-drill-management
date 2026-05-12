<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ER Drill Management') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen text-stone-900" style="font-family: Manrope, sans-serif; background: white;">

        {{-- Background decoration --}}
        <div style="pointer-events: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -10; overflow: hidden;">
            <div style="position: absolute; top: -160px; right: -160px; width: 600px; height: 600px; border-radius: 9999px; background: radial-gradient(circle, rgba(43,45,138,0.07) 0%, transparent 70%);"></div>
            <div style="position: absolute; bottom: -160px; left: -160px; width: 500px; height: 500px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.05) 0%, transparent 70%);"></div>
            <div style="position: absolute; inset: 0; background: linear-gradient(175deg, #F0F3FA 0%, #FFFFFF 45%);"></div>
        </div>

        {{-- Centered login card --}}
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1.5rem;">
            <div style="max-width: 28rem; width: 100%; margin: 0 auto; background: white; border-radius: 1.5rem; padding: 2.75rem 2.5rem; box-shadow: 0 24px 60px -20px rgba(43,45,138,0.18), 0 2px 8px rgba(43,45,138,0.06); border: 1px solid rgba(43,45,138,0.06);">

                {{-- Logo + title --}}
                <a href="/" wire:navigate style="display: block; text-align: center; text-decoration: none; margin-bottom: 2rem;">
                    <x-application-logo class="mx-auto h-12 w-auto max-w-[10rem]" />
                    <div style="margin-top: 1.25rem; font-size: 10px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; color: #2B2D8A;">ER Drill Management</div>
                    <div style="margin-top: 0.5rem; font-size: 1.25rem; font-weight: 700; color: #1A1C5E;">Sign in to continue</div>
                </a>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
