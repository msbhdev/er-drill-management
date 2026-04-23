<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drill_records', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['sto_user_id']);
            $table->dropForeign(['be_user_id']);
            $table->dropForeign(['oim_user_id']);
        });

        Schema::table('drill_workflow_histories', function (Blueprint $table) {
            $table->dropForeign(['actor_user_id']);
        });

        Schema::table('drill_events', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
        });

        Schema::table('drill_actions', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
        });

        Schema::table('drill_attachments', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('drill_records', function (Blueprint $table) {
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('sto_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('be_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('oim_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('drill_workflow_histories', function (Blueprint $table) {
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('drill_events', function (Blueprint $table) {
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('drill_actions', function (Blueprint $table) {
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('drill_attachments', function (Blueprint $table) {
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
