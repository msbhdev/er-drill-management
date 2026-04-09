<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rigs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('drill_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('drill_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('action_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('rig_id')->references('id')->on('rigs')->nullOnDelete();
            $table->index(['role', 'active_status']);
        });

        Schema::create('role_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rig_id')->nullable()->constrained('rigs')->nullOnDelete();
            $table->string('person_name');
            $table->string('role', 50);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['role', 'effective_from']);
        });

        Schema::create('drill_records', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('rig_id')->constrained('rigs')->cascadeOnDelete();
            $table->foreignId('drill_type_id')->constrained('drill_types')->restrictOnDelete();
            $table->foreignId('event_type_id')->constrained('event_types')->restrictOnDelete();
            $table->foreignId('status_id')->constrained('drill_statuses')->restrictOnDelete();
            $table->date('drill_date');
            $table->time('drill_time')->nullable();
            $table->text('on_duty_crews')->nullable();
            $table->string('event_location')->nullable();
            $table->longText('scenario')->nullable();
            $table->string('applicable_dsha')->nullable();
            $table->longText('performance_standard')->nullable();
            $table->string('performance_standards_met', 50)->nullable();
            $table->longText('objectives')->nullable();
            $table->longText('debrief_attendees')->nullable();
            $table->longText('positive_observations')->nullable();
            $table->longText('improvement_opportunities')->nullable();
            $table->longText('other_comments')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sto_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('be_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('oim_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sto_name')->nullable();
            $table->string('be_name')->nullable();
            $table->string('oim_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['rig_id', 'drill_date']);
        });

        Schema::create('drill_workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('drill_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->nullable()->constrained('drill_statuses')->nullOnDelete();
            $table->string('action', 50);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('drill_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->string('event_time', 50)->nullable();
            $table->longText('event_description');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('drill_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->longText('action_description');
            $table->string('action_owner')->nullable();
            $table->foreignId('action_status_id')->constrained('action_statuses')->restrictOnDelete();
            $table->date('due_date')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('drill_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->string('caption')->nullable();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('drill_statuses')->insert([
            ['code' => 'draft', 'name' => 'Draft', 'sort_order' => 10, 'description' => 'Drill is being prepared by STO.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'submitted', 'name' => 'Submitted', 'sort_order' => 20, 'description' => 'Awaiting BE verification.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'returned_by_be', 'name' => 'Returned by BE', 'sort_order' => 30, 'description' => 'Returned to STO for correction by BE.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'verified', 'name' => 'Verified', 'sort_order' => 40, 'description' => 'Verified by BE and awaiting OIM approval.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'returned_by_oim', 'name' => 'Returned by OIM', 'sort_order' => 50, 'description' => 'Returned to STO for correction by OIM.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'approved', 'name' => 'Approved', 'sort_order' => 60, 'description' => 'Approved by OIM.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'closed', 'name' => 'Closed', 'sort_order' => 70, 'description' => 'Closed after follow-up completion.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('action_statuses')->insert([
            ['code' => 'open', 'name' => 'Open', 'sort_order' => 10, 'description' => 'New follow-up action item.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'in_progress', 'name' => 'In Progress', 'sort_order' => 20, 'description' => 'Action is being worked.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'closed', 'name' => 'Closed', 'sort_order' => 30, 'description' => 'Action item has been completed.', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drill_attachments');
        Schema::dropIfExists('drill_actions');
        Schema::dropIfExists('drill_events');
        Schema::dropIfExists('drill_workflow_histories');
        Schema::dropIfExists('drill_records');
        Schema::dropIfExists('role_histories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rig_id']);
            $table->dropIndex(['role', 'active_status']);
        });

        Schema::dropIfExists('action_statuses');
        Schema::dropIfExists('drill_statuses');
        Schema::dropIfExists('event_types');
        Schema::dropIfExists('drill_types');
        Schema::dropIfExists('rigs');
    }
};
