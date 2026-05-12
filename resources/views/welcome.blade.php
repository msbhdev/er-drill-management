<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ER Drill Management') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-stone-900" style="font-family: Manrope, sans-serif;">

        {{-- Background decoration --}}
        <div style="pointer-events: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -10; overflow: hidden;">
            <div style="position: absolute; top: -160px; right: -160px; width: 600px; height: 600px; border-radius: 9999px; background: radial-gradient(circle, rgba(43,45,138,0.07) 0%, transparent 70%);"></div>
            <div style="position: absolute; bottom: -160px; left: -160px; width: 500px; height: 500px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.05) 0%, transparent 70%);"></div>
            <div style="position: absolute; inset: 0; background: linear-gradient(175deg, #F0F3FA 0%, #FFFFFF 45%);"></div>
        </div>

        {{-- Header --}}
        <header style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); border-bottom: 1px solid rgba(43,45,138,0.08);">
            <div style="max-width: 80rem; margin: 0 auto; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <x-application-logo class="h-10 w-auto max-w-[10rem]" />
                    <div style="padding-left: 1rem; border-left: 1px solid rgba(43,45,138,0.15);">
                        <div style="font-size: 10px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; color: #2B2D8A;">ER Drill</div>
                        <div style="font-size: 0.875rem; font-weight: 700; color: #1A1C5E;">Management</div>
                    </div>
                </div>
                <a href="{{ route('login') }}"
                   style="background: #2B2D8A; box-shadow: 0 4px 14px rgba(43,45,138,0.25); border-radius: 9999px; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; color: white; text-decoration: none;">
                    Sign In
                </a>
            </div>
        </header>

        {{-- Main: full-height flex column with everything centered as one group --}}
        <main style="min-height: 100vh; width: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center; padding: 6rem 1.5rem 3rem; text-align: center;">

            {{-- Hero content --}}
            <div style="max-width: 42rem; width: 100%; margin: 0 auto; text-align: center;">

                    {{-- Eyebrow --}}
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 9999px; border: 1px solid rgba(43,45,138,0.2); background: rgba(43,45,138,0.05); padding: 0.375rem 1rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.25em; text-transform: uppercase; color: #2B2D8A;">
                        <span style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #00A3C8; display: inline-block;"></span>
                        Offshore Emergency Response
                    </div>

                    {{-- Headline --}}
                    <h1 style="margin-top: 1.75rem; font-size: clamp(3rem, 6vw, 4rem); font-weight: 800; letter-spacing: -0.02em; line-height: 1.08; color: #1A1C5E;">
                        ER Drill<br>
                        <span style="color: #2B2D8A;">Management</span>
                    </h1>

                    {{-- Subtitle --}}
                    <p style="margin: 1.75rem auto 0; max-width: 38rem; font-size: 1.0625rem; line-height: 1.75; color: #64748b;">
                        Structured drill governance from STO input through BE verification to OIM approval — with full audit trail and rig-based access control.
                    </p>

                    {{-- CTA --}}
                    <div style="margin-top: 2.5rem;">
                        <a href="{{ route('login') }}"
                           style="display: inline-flex; align-items: center; gap: 0.625rem; border-radius: 9999px; padding: 1rem 2rem; font-size: 0.875rem; font-weight: 700; color: white; text-decoration: none; background: #2B2D8A; box-shadow: 0 6px 20px rgba(43,45,138,0.3); transition: opacity 0.15s;">
                            Open Login
                            <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

            </div>

            {{-- Feature strip: sits naturally below hero, centered with it --}}
            <div style="margin: 4rem auto 0; padding-top: 2.5rem; border-top: 1px solid rgba(43,45,138,0.1); max-width: 56rem; width: 100%;">
                <div style="display: flex; flex-wrap: nowrap;">
                    <div style="flex: 1; text-align: center; padding: 0 1rem;">
                        <div style="font-size: 0.625rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #00A3C8;">Workflow</div>
                        <div style="margin-top: 0.4rem; font-size: 0.8125rem; font-weight: 600; color: #1A1C5E;">STO → BE → OIM</div>
                    </div>
                    <div style="flex: 1; text-align: center; padding: 0 1rem; border-left: 1px solid rgba(43,45,138,0.1);">
                        <div style="font-size: 0.625rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #00A3C8;">Evidence</div>
                        <div style="margin-top: 0.4rem; font-size: 0.8125rem; font-weight: 600; color: #1A1C5E;">Photos &amp; Reports</div>
                    </div>
                    <div style="flex: 1; text-align: center; padding: 0 1rem; border-left: 1px solid rgba(43,45,138,0.1);">
                        <div style="font-size: 0.625rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #00A3C8;">Notifications</div>
                        <div style="margin-top: 0.4rem; font-size: 0.8125rem; font-weight: 600; color: #1A1C5E;">Email at Each Step</div>
                    </div>
                    <div style="flex: 1; text-align: center; padding: 0 1rem; border-left: 1px solid rgba(43,45,138,0.1);">
                        <div style="font-size: 0.625rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #00A3C8;">Exports</div>
                        <div style="margin-top: 0.4rem; font-size: 0.8125rem; font-weight: 600; color: #1A1C5E;">PDF Reports</div>
                    </div>
                </div>
            </div>

        </main>

    </body>
</html>
