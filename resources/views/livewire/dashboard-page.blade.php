<div class="space-y-8">
    <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-8 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="text-sm font-semibold uppercase tracking-[0.28em] text-stone-500">Operations Dashboard</div>
            <h1 class="mt-3 text-3xl font-extrabold text-stone-950">
                Welcome back, {{ auth()->user()->full_name }}
            </h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">
                You are signed in as <span class="font-semibold text-stone-900">{{ auth()->user()->role }}</span>
                @if (auth()->user()->rig)
                    for <span class="font-semibold text-stone-900">{{ auth()->user()->rig->name }}</span>.
                @endif
                Use this dashboard to monitor outstanding drill work, approvals, and follow-up actions.
            </p>
        </div>

        <div class="rounded-[2rem] border border-stone-900/10 bg-stone-950 p-8 text-white shadow-xl shadow-stone-900/20">
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-amber-200">Quick Actions</div>
            <div class="mt-6 grid gap-3">
                <a href="{{ route('drills.index') }}" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                    Open Drill Queue
                </a>
                @can('create', \App\Models\DrillRecord::class)
                    <a href="{{ route('drills.create') }}" class="rounded-2xl bg-amber-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">
                        Create New Drill
                    </a>
                @endcan
                @if (in_array(auth()->user()->role, ['RM', 'Management', 'Administrator'], true))
                    <a href="{{ route('reports.index') }}" class="rounded-2xl border border-white/20 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Open Reports
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Total drills</div>
            <div class="mt-3 text-4xl font-extrabold text-stone-950">{{ $stats['total_drills'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Awaiting your action</div>
            <div class="mt-3 text-4xl font-extrabold text-amber-700">{{ $stats['awaiting_action'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Approved this month</div>
            <div class="mt-3 text-4xl font-extrabold text-emerald-700">{{ $stats['approved_this_month'] }}</div>
        </div>
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-lg shadow-stone-900/5">
            <div class="text-sm font-semibold text-stone-500">Overdue actions</div>
            <div class="mt-3 text-4xl font-extrabold text-rose-700">{{ $stats['overdue_actions'] }}</div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-white/80 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-stone-950">Recent Drill Records</h2>
                <p class="mt-1 text-sm text-stone-600">Latest drill activity that matches your rig access.</p>
            </div>
            <a href="{{ route('drills.index') }}" wire:navigate class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">
                View All
            </a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead>
                    <tr class="text-left text-stone-500">
                        <th class="pb-3 font-semibold">Reference</th>
                        <th class="pb-3 font-semibold">Rig</th>
                        <th class="pb-3 font-semibold">Drill Type</th>
                        <th class="pb-3 font-semibold">Date</th>
                        <th class="pb-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($recentDrills as $drill)
                        <tr class="text-stone-700">
                            <td class="py-4 font-semibold text-stone-900">
                                <a href="{{ route('drills.show', $drill) }}" wire:navigate class="hover:text-teal-700">{{ $drill->reference_no }}</a>
                            </td>
                            <td class="py-4">{{ $drill->rig->name }}</td>
                            <td class="py-4">{{ $drill->drillTypeNames() }}</td>
                            <td class="py-4">{{ $drill->drill_date?->format('d M Y') }}</td>
                            <td class="py-4">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-700">{{ $drill->status->name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-stone-500">No drills available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
