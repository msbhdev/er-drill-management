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
    <body class="font-sans antialiased text-stone-900" style="font-family: Manrope, sans-serif;">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(15,118,110,0.2),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(217,119,6,0.18),_transparent_24%),linear-gradient(180deg,_#111827_0%,_#1f2937_100%)] px-4 py-10">
            <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl items-center gap-10 lg:grid-cols-[1.2fr_0.9fr]">
                <div class="text-white">
                    <div class="mb-6 inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                        Offshore Emergency Response Workflow
                    </div>
                    <h1 class="max-w-2xl text-4xl font-extrabold tracking-tight sm:text-5xl">
                        Coordinate drills, approvals, and rig reporting from one control room.
                    </h1>
                    <p class="mt-5 max-w-xl text-lg leading-8 text-slate-200">
                        ER Drill Management centralizes STO submissions, BE verification, OIM approval, and cross-rig reporting with traceable audit history.
                    </p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm font-semibold text-amber-200">Workflow</div>
                            <div class="mt-2 text-sm text-slate-200">Draft, verify, approve, and close with comments and email alerts.</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm font-semibold text-amber-200">Rig Control</div>
                            <div class="mt-2 text-sm text-slate-200">Rig-specific access for STO, BE, OIM, and RM with shared role accounts.</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm font-semibold text-amber-200">Reporting</div>
                            <div class="mt-2 text-sm text-slate-200">Interactive dashboards plus Excel and PDF exports for management.</div>
                        </div>
                    </div>
                </div>

                <div class="w-full rounded-[2rem] border border-white/10 bg-white p-8 shadow-2xl shadow-black/30">
                    <a href="/" wire:navigate class="mb-8 flex items-center gap-3">
                        <x-application-logo class="h-14 w-14" />
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">ER Drill Management</div>
                            <div class="text-lg font-bold text-stone-900">Sign in to continue</div>
                        </div>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
