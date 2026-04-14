<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drill_record_drill_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->foreignId('drill_type_id')->constrained('drill_types')->restrictOnDelete();
            $table->unique(['drill_record_id', 'drill_type_id']);
        });

        Schema::create('drill_record_event_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->foreignId('event_type_id')->constrained('event_types')->restrictOnDelete();
            $table->unique(['drill_record_id', 'event_type_id']);
        });

        DB::table('drill_records')
            ->select('id as drill_record_id', 'drill_type_id')
            ->orderBy('id')
            ->chunk(100, function ($records): void {
                $payload = collect($records)
                    ->filter(fn ($record) => $record->drill_type_id !== null)
                    ->map(fn ($record) => [
                        'drill_record_id' => $record->drill_record_id,
                        'drill_type_id' => $record->drill_type_id,
                    ])
                    ->all();

                if ($payload !== []) {
                    DB::table('drill_record_drill_type')->insert($payload);
                }
            });

        DB::table('drill_records')
            ->select('id as drill_record_id', 'event_type_id')
            ->orderBy('id')
            ->chunk(100, function ($records): void {
                $payload = collect($records)
                    ->filter(fn ($record) => $record->event_type_id !== null)
                    ->map(fn ($record) => [
                        'drill_record_id' => $record->drill_record_id,
                        'event_type_id' => $record->event_type_id,
                    ])
                    ->all();

                if ($payload !== []) {
                    DB::table('drill_record_event_type')->insert($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('drill_record_event_type');
        Schema::dropIfExists('drill_record_drill_type');
    }
};
