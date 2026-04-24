<?php

namespace App\Console\Commands;

use App\Services\UserListSyncService;
use Illuminate\Console\Command;

class SyncUserListCommand extends Command
{
    protected $signature = 'er-drill:sync-user-list {--path=} {--password=password}';

    protected $description = 'Sync ER Drill rigs and shared accounts from the configured Excel user list.';

    public function handle(UserListSyncService $service): int
    {
        $path = $this->option('path') ?: config('er_drill.user_list_path');
        $password = (string) $this->option('password');

        $summary = $service->syncFromWorkbook($path, $password);

        $this->components->info('ER Drill user list sync completed.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Rigs', $summary['rigs_count']],
                ['Accounts', $summary['accounts_count']],
            ]
        );

        return self::SUCCESS;
    }
}
