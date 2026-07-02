<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DSHA (Defined Situations of Hazard & Accident) master list and the
     * pivot linking them to drill records (a drill may cite several DSHAs).
     * The legacy free-text `drill_records.applicable_dsha` column is left in
     * place so historical records keep their original text.
     */
    public function up(): void
    {
        Schema::create('dshas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('drill_record_dsha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_record_id')->constrained('drill_records')->cascadeOnDelete();
            $table->foreignId('dsha_id')->constrained('dshas')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['drill_record_id', 'dsha_id']);
        });

        $now = now();
        $rows = [
            ['BT.001', 'Hydrocarbons in Formation'],
            ['BT.002', 'Refined Hydrocarbon'],
            ['BT.003', 'Other Flammable Materials'],
            ['BT.004', 'Explosive Charges'],
            ['BT.005', 'Toxic Gas (H2S)'],
            ['BT.006', 'Unsafe Atmospheres in Confined Space'],
            ['BT.007', 'Personnel Working at Height'],
            ['BT.008', 'Containment Vessels Under High Pressure'],
            ['BT.009', 'Objects at Height (Drilling Operations)'],
            ['BT.010', 'Objects at Height (Crane Operations)'],
            ['BT.011', 'Dynamic Situation Hazards'],
            ['BT.012', 'Hazardous Goods'],
            ['BT.013', 'Facility Station Keeping at Sea'],
            ['BT.014', 'Facility in Transit at Sea'],
            ['BT.015', 'Helicopter in Transit to and from Facility'],
            ['BT.016', 'Helicopter Arrival and Departure at Facility'],
            ['BT.017', 'Other Marine Vessels and Obstacles'],
            ['BT.018', 'Dependence on Escape, Evacuation and Rescue Systems'],
            ['BT.019', 'Dependence on Medical Services'],
            ['BT.020', 'Dependence on Environmental Conditions'],
            ['BT.021', 'Dependence on Equipment'],
            ['BT.022', 'Dependence on Human Performance'],
        ];

        DB::table('dshas')->insert(array_map(fn ($row) => [
            'code' => $row[0],
            'name' => $row[1],
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));
    }

    public function down(): void
    {
        Schema::dropIfExists('drill_record_dsha');
        Schema::dropIfExists('dshas');
    }
};
