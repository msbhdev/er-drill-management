<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('auth');

        if (! $schema->hasTable('accounts')) {
            $schema->create('accounts', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('email')->unique();
                $table->string('account_type', 50)->default('shared_role');
                $table->string('role_code', 50)->nullable();
                $table->string('rig_code', 50)->nullable()->index();
                $table->boolean('active_status')->default(true);
                $table->string('description')->nullable();
                $table->string('password');
                $table->timestamp('last_login_at')->nullable();
                $table->unsignedInteger('failed_login_attempts')->default(0);
                $table->timestamp('locked_until')->nullable();
                $table->boolean('must_change_password')->default(true);
                $table->timestamp('name_confirmed_at')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index(['role_code', 'rig_code', 'active_status'], 'accounts_role_rig_active_idx');
            });
        }

        if (! $schema->hasTable('account_app_access')) {
            $schema->create('account_app_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
                $table->string('app_code', 100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['account_id', 'app_code'], 'acct_app_access_account_app_uq');
                $table->index(['app_code', 'is_active'], 'acct_app_access_app_active_idx');
            });
        }

        if (! $schema->hasTable('role_assignee_schedules')) {
            $schema->create('role_assignee_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
                $table->string('person_name');
                $table->string('role_code', 50)->nullable();
                $table->string('rig_code', 50)->nullable();
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->text('remarks')->nullable();
                $table->boolean('active_status')->default(true);
                $table->timestamps();

                $table->index(['account_id', 'effective_from', 'active_status'], 'role_sched_account_from_active_idx');
            });
        }

        if (! $schema->hasTable('password_reset_tokens')) {
            $schema->create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('auth');

        $schema->dropIfExists('role_assignee_schedules');
        $schema->dropIfExists('account_app_access');

        if ($schema->hasTable('accounts')) {
            $schema->drop('accounts');
        }

        if ($schema->hasTable('password_reset_tokens')) {
            $schema->drop('password_reset_tokens');
        }
    }
};
