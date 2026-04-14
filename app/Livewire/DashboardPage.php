<?php

namespace App\Livewire;

use App\Models\DrillAction;
use App\Models\DrillRecord;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render()
    {
        $user = auth()->user();

        $baseQuery = DrillRecord::query()->visibleTo($user);
        $recentDrills = (clone $baseQuery)
            ->with(['rig', 'drillTypes', 'status'])
            ->latest('drill_date')
            ->limit(6)
            ->get();

        $awaitingAction = match ($user->role) {
            'STO' => (clone $baseQuery)->whereHas('status', fn ($query) => $query->whereIn('code', ['draft', 'returned_by_be', 'returned_by_oim']))->count(),
            'BE' => (clone $baseQuery)->whereHas('status', fn ($query) => $query->where('code', 'submitted'))->count(),
            'OIM' => (clone $baseQuery)->whereHas('status', fn ($query) => $query->whereIn('code', ['verified', 'approved']))->count(),
            'RM', 'Management', 'Administrator' => (clone $baseQuery)->whereHas('status', fn ($query) => $query->where('code', 'approved'))->count(),
            default => 0,
        };

        $overdueActions = DrillAction::query()
            ->whereHas('drillRecord', fn ($query) => $query->visibleTo($user))
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereHas('status', fn ($query) => $query->where('code', '!=', 'closed'))
            ->count();

        return view('livewire.dashboard-page', [
            'stats' => [
                'total_drills' => (clone $baseQuery)->count(),
                'awaiting_action' => $awaitingAction,
                'approved_this_month' => (clone $baseQuery)->whereMonth('approved_at', now()->month)->whereYear('approved_at', now()->year)->count(),
                'overdue_actions' => $overdueActions,
            ],
            'recentDrills' => $recentDrills,
        ])->layout('layouts.app');
    }
}
