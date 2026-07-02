<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repurpose Event Type into a Drill / Real Event classification.
     * Adds the two new active types and deactivates the legacy emergency
     * types (Fire, Gas Release, Man Overboard) so existing drill records
     * that still reference them keep displaying correctly.
     */
    public function up(): void
    {
        foreach ([
            'Drill' => 'Planned drill exercise',
            'Real Event' => 'Actual emergency event',
        ] as $name => $description) {
            if (DB::table('event_types')->where('name', $name)->exists()) {
                DB::table('event_types')
                    ->where('name', $name)
                    ->update(['is_active' => true, 'updated_at' => now()]);

                continue;
            }

            DB::table('event_types')->insert([
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('event_types')
            ->whereIn('name', ['Fire', 'Gas Release', 'Man Overboard'])
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('event_types')
            ->whereIn('name', ['Fire', 'Gas Release', 'Man Overboard'])
            ->update(['is_active' => true, 'updated_at' => now()]);

        DB::table('event_types')
            ->whereIn('name', ['Drill', 'Real Event'])
            ->update(['is_active' => false, 'updated_at' => now()]);
    }
};
