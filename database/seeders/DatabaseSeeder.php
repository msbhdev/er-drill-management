<?php

namespace Database\Seeders;

use App\Models\DrillType;
use App\Models\EventType;
use App\Services\UserListSyncService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Fire Drill', 'description' => 'Emergency fire response drill'],
            ['name' => 'Muster Drill', 'description' => 'Evacuation and accountability drill'],
            ['name' => 'Medical Drill', 'description' => 'Emergency medical response simulation'],
        ] as $type) {
            DrillType::query()->firstOrCreate(['name' => $type['name']], $type);
        }

        foreach ([
            ['name' => 'Drill', 'description' => 'Planned drill exercise'],
            ['name' => 'Real Event', 'description' => 'Actual emergency event'],
        ] as $type) {
            EventType::query()->firstOrCreate(['name' => $type['name']], $type);
        }

        $path = config('er_drill.user_list_path');

        if (blank($path) || ! file_exists($path)) {
            $this->command?->warn('ER Drill user list workbook not found. Skipping rig and shared-account sync.');

            return;
        }

        app(UserListSyncService::class)->syncFromWorkbook($path);
    }
}
