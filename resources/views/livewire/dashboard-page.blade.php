@php
    $user = auth()->user();
    $statusStyles = [
        'draft'          => ['bg' => 'rgba(100,116,139,0.1)', 'fg' => '#475569'],
        'submitted'      => ['bg' => 'rgba(0,163,200,0.12)',  'fg' => '#0a6f87'],
        'returned_by_be' => ['bg' => 'rgba(232,118,42,0.12)', 'fg' => '#a85416'],
        'verified'       => ['bg' => 'rgba(43,45,138,0.1)',   'fg' => '#2B2D8A'],
        'returned_by_oim'=> ['bg' => 'rgba(232,118,42,0.12)', 'fg' => '#a85416'],
        'approved'       => ['bg' => 'rgba(123,63,184,0.12)', 'fg' => '#5b2890'],
        'closed'         => ['bg' => 'rgba(16,185,129,0.12)', 'fg' => '#047857'],
    ];
@endphp

<div style="display: flex; flex-direction: column; gap: 2rem;">

    {{-- Hero + Quick Actions row --}}
    <section style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 1.5rem;" class="dashboard-hero-grid">

        {{-- Greeting card --}}
        <div style="position: relative; overflow: hidden; border-radius: 1.75rem; border: 1px solid rgba(43,45,138,0.08); background: white; padding: 2.25rem; box-shadow: 0 12px 40px -16px rgba(43,45,138,0.15), 0 2px 6px rgba(43,45,138,0.04);">
            {{-- Decorative gem accents inspired by the Vantris logo --}}
            <div style="position: absolute; top: -40px; right: -40px; width: 180px; height: 180px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.12) 0%, transparent 70%); pointer-events: none;"></div>
            <div style="position: absolute; bottom: -60px; right: 80px; width: 140px; height: 140px; border-radius: 9999px; background: radial-gradient(circle, rgba(123,63,184,0.08) 0%, transparent 70%); pointer-events: none;"></div>

            <div style="position: relative;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 9999px; border: 1px solid rgba(43,45,138,0.18); background: rgba(43,45,138,0.04); padding: 0.3rem 0.875rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #2B2D8A;">
                    <span style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #00A3C8;"></span>
                    Operations Dashboard
                </div>
                <h1 style="margin-top: 1.25rem; font-size: 1.875rem; font-weight: 800; line-height: 1.15; letter-spacing: -0.01em; color: #1A1C5E;">
                    Welcome back,<br>{{ $user->full_name }}
                </h1>
                <p style="margin-top: 1rem; max-width: 36rem; font-size: 0.9375rem; line-height: 1.7; color: #475569;">
                    You are signed in as <span style="font-weight: 700; color: #2B2D8A;">{{ $user->role }}</span>@if ($user->rig) for <span style="font-weight: 700; color: #2B2D8A;">{{ $user->rig->name }}</span>@endif. Monitor outstanding drills, approvals, and follow-up actions from one place.
                </p>
            </div>
        </div>

        {{-- Quick Actions card --}}
        <div style="position: relative; overflow: hidden; border-radius: 1.75rem; padding: 2.25rem; background: linear-gradient(160deg, #1A1C5E 0%, #2B2D8A 100%); color: white; box-shadow: 0 20px 50px -20px rgba(43,45,138,0.45);">
            <div style="position: absolute; top: -80px; right: -80px; width: 240px; height: 240px; border-radius: 9999px; background: radial-gradient(circle, rgba(0,163,200,0.25) 0%, transparent 70%);"></div>
            <div style="position: absolute; bottom: -100px; left: -50px; width: 200px; height: 200px; border-radius: 9999px; background: radial-gradient(circle, rgba(123,63,184,0.25) 0%, transparent 70%);"></div>

            <div style="position: relative;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #7ED4EA;">
                    <span style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #00A3C8;"></span>
                    Quick Actions
                </div>
                <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.625rem;">
                    <a href="{{ route('drills.index') }}" wire:navigate
                       style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; border-radius: 0.875rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.08); padding: 0.875rem 1.125rem; font-size: 0.875rem; font-weight: 600; color: white; text-decoration: none; transition: background 0.15s;"
                       onmouseover="this.style.background='rgba(255,255,255,0.14)';"
                       onmouseout="this.style.background='rgba(255,255,255,0.08)';">
                        <span>Open Drill Queue</span>
                        <span style="opacity: 0.6;">→</span>
                    </a>
                    @can('create', \App\Models\DrillRecord::class)
                        <a href="{{ route('drills.create') }}" wire:navigate
                           style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; border-radius: 0.875rem; background: #00A3C8; padding: 0.875rem 1.125rem; font-size: 0.875rem; font-weight: 700; color: white; text-decoration: none; box-shadow: 0 6px 18px rgba(0,163,200,0.35); transition: opacity 0.15s;"
                           onmouseover="this.style.opacity='0.9';"
                           onmouseout="this.style.opacity='1';">
                            <span>Create New Drill</span>
                            <span>+</span>
                        </a>
                    @endcan
                    @if (in_array($user->role, ['RM', 'Management', 'Administrator'], true))
                        <a href="{{ route('reports.index') }}" wire:navigate
                           style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; border-radius: 0.875rem; border: 1px solid rgba(255,255,255,0.2); padding: 0.875rem 1.125rem; font-size: 0.875rem; font-weight: 600; color: white; text-decoration: none; transition: background 0.15s;"
                           onmouseover="this.style.background='rgba(255,255,255,0.08)';"
                           onmouseout="this.style.background='transparent';">
                            <span>Open Reports</span>
                            <span style="opacity: 0.6;">→</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Stat strip --}}
    @php
        $stats_cards = [
            ['label' => 'Total Drills',         'value' => $stats['total_drills'],        'accent' => '#2B2D8A', 'tint' => 'rgba(43,45,138,0.08)'],
            ['label' => 'Awaiting Your Action', 'value' => $stats['awaiting_action'],     'accent' => '#E8762A', 'tint' => 'rgba(232,118,42,0.10)'],
            ['label' => 'Approved This Month',  'value' => $stats['approved_this_month'], 'accent' => '#7B3FB8', 'tint' => 'rgba(123,63,184,0.10)'],
            ['label' => 'Overdue Actions',      'value' => $stats['overdue_actions'],     'accent' => '#DC2626', 'tint' => 'rgba(220,38,38,0.08)'],
        ];
    @endphp
    <section style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;" class="dashboard-stats-grid">
        @foreach ($stats_cards as $card)
            <div style="position: relative; overflow: hidden; border-radius: 1.25rem; border: 1px solid rgba(43,45,138,0.08); background: white; padding: 1.5rem; box-shadow: 0 8px 24px -12px rgba(43,45,138,0.08);">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: {{ $card['accent'] }};"></div>
                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; border-radius: 9999px; background: {{ $card['tint'] }};"></div>
                <div style="position: relative;">
                    <div style="font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #64748b;">{{ $card['label'] }}</div>
                    <div style="margin-top: 0.875rem; font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em; color: {{ $card['accent'] }};">{{ $card['value'] }}</div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Recent Drill Records --}}
    <section style="border-radius: 1.75rem; border: 1px solid rgba(43,45,138,0.08); background: white; padding: 1.75rem; box-shadow: 0 12px 40px -20px rgba(43,45,138,0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #00A3C8;">Recent Activity</div>
                <h2 style="margin-top: 0.4rem; font-size: 1.25rem; font-weight: 800; color: #1A1C5E;">Drill Records</h2>
                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #64748b;">Latest drill activity that matches your rig access.</p>
            </div>
            <a href="{{ route('drills.index') }}" wire:navigate
               style="border-radius: 9999px; border: 1px solid rgba(43,45,138,0.2); padding: 0.5rem 1.125rem; font-size: 0.8125rem; font-weight: 600; color: #2B2D8A; text-decoration: none; transition: all 0.15s;"
               onmouseover="this.style.background='#2B2D8A'; this.style.color='white';"
               onmouseout="this.style.background='transparent'; this.style.color='#2B2D8A';">
                View All →
            </a>
        </div>

        <div style="margin-top: 1.5rem; overflow-x: auto;">
            <table style="min-width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="text-align: left;">
                        <th style="padding: 0.5rem 0.75rem 0.75rem 0; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(43,45,138,0.08);">Reference</th>
                        <th style="padding: 0.5rem 0.75rem 0.75rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(43,45,138,0.08);">Rig</th>
                        <th style="padding: 0.5rem 0.75rem 0.75rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(43,45,138,0.08);">Drill Type</th>
                        <th style="padding: 0.5rem 0.75rem 0.75rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(43,45,138,0.08);">Date</th>
                        <th style="padding: 0.5rem 0 0.75rem 0.75rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(43,45,138,0.08);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentDrills as $drill)
                        @php $st = $statusStyles[$drill->status->code] ?? ['bg' => 'rgba(100,116,139,0.1)', 'fg' => '#475569']; @endphp
                        <tr style="border-bottom: 1px solid rgba(43,45,138,0.05);">
                            <td style="padding: 1rem 0.75rem 1rem 0; font-weight: 700;">
                                <a href="{{ route('drills.show', $drill) }}" wire:navigate style="color: #2B2D8A; text-decoration: none;">{{ $drill->reference_no }}</a>
                            </td>
                            <td style="padding: 1rem 0.75rem; color: #475569;">{{ $drill->rig->name }}</td>
                            <td style="padding: 1rem 0.75rem; color: #475569;">{{ $drill->drillTypeNames() }}</td>
                            <td style="padding: 1rem 0.75rem; color: #475569;">{{ $drill->drill_date?->format('d M Y') }}</td>
                            <td style="padding: 1rem 0 1rem 0.75rem;">
                                <span style="display: inline-block; border-radius: 9999px; background: {{ $st['bg'] }}; color: {{ $st['fg'] }}; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase;">{{ $drill->status->name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 2.5rem 0; text-align: center; color: #94a3b8;">No drills available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        @media (max-width: 900px) {
            .dashboard-hero-grid { grid-template-columns: 1fr !important; }
            .dashboard-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 520px) {
            .dashboard-stats-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</div>
