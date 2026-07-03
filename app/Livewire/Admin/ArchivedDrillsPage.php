<?php

namespace App\Livewire\Admin;

use App\Models\ArchivedDrillRecord;
use Livewire\Component;
use Livewire\WithPagination;

class ArchivedDrillsPage extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $viewingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdministrator(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewArchive(int $id): void
    {
        $this->viewingId = $id;
    }

    public function closeArchive(): void
    {
        $this->viewingId = null;
    }

    public function render()
    {
        $archives = ArchivedDrillRecord::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('reference_no', 'like', $term)
                        ->orWhere('rig_name', 'like', $term)
                        ->orWhere('deleted_by_name', 'like', $term);
                });
            })
            ->orderByDesc('archived_at')
            ->paginate(15);

        return view('livewire.admin.archived-drills-page', [
            'archives' => $archives,
            'viewing' => $this->viewingId ? ArchivedDrillRecord::query()->find($this->viewingId) : null,
        ])->layout('layouts.app');
    }
}
