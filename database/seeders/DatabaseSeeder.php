<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\DrillType;
use App\Models\EventType;
use App\Models\Rig;
use App\Models\RoleHistory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rigs = collect([
            ['name' => 'Rig Alpha', 'code' => 'RAL', 'location' => 'Sarawak'],
            ['name' => 'Rig Bravo', 'code' => 'RBR', 'location' => 'Sabah'],
        ])->map(fn (array $rig) => Rig::query()->firstOrCreate(['code' => $rig['code']], $rig));

        foreach ([
            ['name' => 'Fire Drill', 'description' => 'Emergency fire response drill'],
            ['name' => 'Muster Drill', 'description' => 'Evacuation and accountability drill'],
            ['name' => 'Medical Drill', 'description' => 'Emergency medical response simulation'],
        ] as $type) {
            DrillType::query()->firstOrCreate(['name' => $type['name']], $type);
        }

        foreach ([
            ['name' => 'Fire', 'description' => 'Fire or smoke related event'],
            ['name' => 'Gas Release', 'description' => 'Gas detection and response event'],
            ['name' => 'Man Overboard', 'description' => 'Marine rescue event'],
        ] as $type) {
            EventType::query()->firstOrCreate(['name' => $type['name']], $type);
        }

        $accounts = [
            ['full_name' => 'System Administrator', 'email' => 'admin@erdrill.local', 'role' => UserRole::Administrator->value, 'rig_id' => null],
            ['full_name' => 'Management Viewer', 'email' => 'management@erdrill.local', 'role' => UserRole::Management->value, 'rig_id' => null],
            ['full_name' => 'STO Rig Alpha', 'email' => 'sto.alpha@erdrill.local', 'role' => UserRole::STO->value, 'rig_id' => $rigs[0]->id],
            ['full_name' => 'BE Rig Alpha', 'email' => 'be.alpha@erdrill.local', 'role' => UserRole::BE->value, 'rig_id' => $rigs[0]->id],
            ['full_name' => 'OIM Rig Alpha', 'email' => 'oim.alpha@erdrill.local', 'role' => UserRole::OIM->value, 'rig_id' => $rigs[0]->id],
            ['full_name' => 'RM Rig Alpha', 'email' => 'rm.alpha@erdrill.local', 'role' => UserRole::RM->value, 'rig_id' => $rigs[0]->id],
            ['full_name' => 'STO Rig Bravo', 'email' => 'sto.bravo@erdrill.local', 'role' => UserRole::STO->value, 'rig_id' => $rigs[1]->id],
            ['full_name' => 'BE Rig Bravo', 'email' => 'be.bravo@erdrill.local', 'role' => UserRole::BE->value, 'rig_id' => $rigs[1]->id],
            ['full_name' => 'OIM Rig Bravo', 'email' => 'oim.bravo@erdrill.local', 'role' => UserRole::OIM->value, 'rig_id' => $rigs[1]->id],
            ['full_name' => 'RM Rig Bravo', 'email' => 'rm.bravo@erdrill.local', 'role' => UserRole::RM->value, 'rig_id' => $rigs[1]->id],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    ...$account,
                    'active_status' => true,
                    'description' => 'Seeded shared role account',
                    'password' => Hash::make('password'),
                    'must_change_password' => false,
                    'name_confirmed_at' => now(),
                ]
            );

            if ($user->rig_id) {
                RoleHistory::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'effective_to' => null,
                    ],
                    [
                        'rig_id' => $user->rig_id,
                        'person_name' => $user->full_name,
                        'role' => $user->role,
                        'effective_from' => now()->toDateString(),
                    ]
                );
            }
        }
    }
}
