<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit archive for permanently deleted drills. When an administrator
     * deletes a drill record, a complete JSON snapshot of the record and all
     * of its related data (events, actions, attachments, workflow history,
     * assigned people) is written here first, together with who deleted it and
     * when. There is no foreign key to `drill_records` on purpose — the row it
     * describes is gone by design.
     */
    public function up(): void
    {
        Schema::create('archived_drill_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_drill_id')->nullable();
            $table->string('reference_no')->index();
            $table->string('rig_name')->nullable();
            $table->string('status_name')->nullable();
            $table->date('drill_date')->nullable();
            $table->longText('snapshot');
            $table->unsignedBigInteger('deleted_by_user_id')->nullable();
            $table->string('deleted_by_name')->nullable();
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_drill_records');
    }
};
