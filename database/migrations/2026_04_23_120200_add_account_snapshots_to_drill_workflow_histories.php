<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drill_workflow_histories', function (Blueprint $table) {
            $table->string('actor_account_name')->nullable()->after('actor_user_id');
            $table->string('actor_person_name')->nullable()->after('actor_account_name');
            $table->string('actor_role_code', 50)->nullable()->after('actor_person_name');
            $table->string('actor_rig_code', 50)->nullable()->after('actor_role_code');
        });
    }

    public function down(): void
    {
        Schema::table('drill_workflow_histories', function (Blueprint $table) {
            $table->dropColumn([
                'actor_account_name',
                'actor_person_name',
                'actor_role_code',
                'actor_rig_code',
            ]);
        });
    }
};
