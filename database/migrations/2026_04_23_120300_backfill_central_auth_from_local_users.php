<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::connection('auth')->hasTable('accounts')) {
            return;
        }

        $accounts = DB::connection('auth')->table('accounts');

        if ($accounts->count() > 0) {
            return;
        }

        $rigCodes = DB::table('rigs')
            ->pluck('code', 'id');

        $localUsers = DB::table('users')->orderBy('id')->get();

        foreach ($localUsers as $user) {
            $rigCode = $user->rig_id ? $rigCodes->get($user->rig_id) : null;

            $accounts->insert([
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'account_type' => in_array($user->role, ['Administrator', 'Management'], true) ? 'admin' : 'shared_role',
                'role_code' => $user->role,
                'rig_code' => $rigCode,
                'active_status' => $user->active_status,
                'description' => $user->description,
                'password' => $user->password,
                'last_login_at' => $user->last_login_at,
                'failed_login_attempts' => $user->failed_login_attempts,
                'locked_until' => $user->locked_until,
                'must_change_password' => $user->must_change_password,
                'name_confirmed_at' => $user->name_confirmed_at,
                'remember_token' => $user->remember_token,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            DB::connection('auth')->table('account_app_access')->insert([
                'account_id' => $user->id,
                'app_code' => config('er_drill.auth_app_code', 'er_drill'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($rigCode && $user->role) {
                DB::connection('auth')->table('role_assignee_schedules')->insert([
                    'account_id' => $user->id,
                    'person_name' => $user->full_name,
                    'role_code' => $user->role,
                    'rig_code' => $rigCode,
                    'effective_from' => optional($user->created_at)->toDateString() ?? now()->toDateString(),
                    'effective_to' => null,
                    'remarks' => 'Migrated from local users table.',
                    'active_status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally left blank to preserve migrated central-auth records.
    }
};
