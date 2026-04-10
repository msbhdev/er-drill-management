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
    <body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(15,118,110,0.15),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(217,119,6,0.18),_transparent_26%),linear-gradient(180deg,_#faf7f2_0%,_#f5efe4_55%,_#efe2ce_100%)] text-stone-900" style="font-family: Manrope, sans-serif;">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <x-application-logo class="h-12 w-auto max-w-[12rem]" />
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">ER Drill</div>
                        <div class="text-lg font-bold text-stone-900">Management</div>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-stone-900/15">Sign In</a>
            </div>

            <section class="mt-16 grid gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-900">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Drill governance for offshore operations
                    </div>
                    <h1 class="mt-6 max-w-3xl text-5xl font-extrabold tracking-tight text-stone-950 sm:text-6xl">
                        One system for STO input, BE verification, OIM approval, and rig-wide reporting.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-700">
                        ER Drill Management keeps every exercise traceable from draft through closure, with rig-based access, attached evidence, corrective actions, and export-ready reports for leadership.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('login') }}" class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white shadow-xl shadow-stone-900/15">Open Login</a>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/80 bg-white/85 p-8 shadow-2xl shadow-stone-900/10 backdrop-blur">
                    <div class="grid gap-5">
                        <div class="rounded-3xl bg-stone-950 p-6 text-white">
                            <div class="text-sm font-semibold text-amber-200">Workflow routing</div>
                            <div class="mt-3 text-2xl font-bold">STO -> BE -> OIM</div>
                            <p class="mt-2 text-sm leading-6 text-stone-300">Submit, verify, approve, and close with role-specific permissions and audit history.</p>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                                <div class="text-sm font-semibold text-stone-500">Attachments</div>
                                <div class="mt-2 text-lg font-bold text-stone-900">Photos and reports</div>
                            </div>
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                                <div class="text-sm font-semibold text-stone-500">Notifications</div>
                                <div class="mt-2 text-lg font-bold text-stone-900">Email at each approval step</div>
                            </div>
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                                <div class="text-sm font-semibold text-stone-500">Exports</div>
                                <div class="mt-2 text-lg font-bold text-stone-900">Excel and PDF outputs</div>
                            </div>
                            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                                <div class="text-sm font-semibold text-stone-500">Rig access</div>
                                <div class="mt-2 text-lg font-bold text-stone-900">Scoped by assignment</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mt-20 grid gap-6 md:grid-cols-3">
                <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Operational</div>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">Drill Execution</h2>
                    <p class="mt-3 text-sm leading-7 text-stone-600">Capture drill details, timeline events, follow-up actions, and supporting files in one structured record.</p>
                </div>
                <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Approval</div>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">Controlled Review</h2>
                    <p class="mt-3 text-sm leading-7 text-stone-600">Keep BE and OIM review gates separate with return comments, status tracking, and full workflow history.</p>
                </div>
                <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Leadership</div>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">Rig Reporting</h2>
                    <p class="mt-3 text-sm leading-7 text-stone-600">RM and Management get filtered dashboards, overdue action tracking, and export-ready reporting across rigs.</p>
                </div>
            </section>

        </div>
    </body>
</html>
