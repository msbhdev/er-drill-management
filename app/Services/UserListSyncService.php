<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AccountAppAccess;
use App\Models\Rig;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserListSyncService
{
    public function syncFromWorkbook(string $path, string $defaultPassword = 'password'): array
    {
        $payload = $this->parseWorkbook($path);
        $appCode = config('er_drill.auth_app_code');
        $today = now()->toDateString();

        $rigs = collect($payload['rigs'])
            ->map(function (array $rig) {
                return Rig::query()->updateOrCreate(
                    ['code' => $rig['code']],
                    [
                        'name' => $rig['name'],
                        'location' => $rig['location'],
                        'timezone' => $rig['timezone'],
                        'is_active' => true,
                    ]
                );
            })
            ->keyBy('code');

        Rig::query()
            ->whereNotIn('code', $rigs->keys()->all())
            ->get()
            ->each(function (Rig $rig) {
                if ($rig->drillRecords()->exists()) {
                    $rig->update(['is_active' => false]);

                    return;
                }

                $rig->delete();
            });

        $desiredEmails = collect($payload['accounts'])
            ->pluck('email')
            ->map(fn (string $email) => Str::lower($email))
            ->all();

        $existingAppAccounts = User::query()
            ->whereHas('appAccesses', function ($query) use ($appCode) {
                $query->where('app_code', $appCode);
            })
            ->get();

        foreach ($existingAppAccounts as $account) {
            if (in_array(Str::lower($account->email), $desiredEmails, true)) {
                continue;
            }

            $account->appAccesses()
                ->where('app_code', $appCode)
                ->delete();

            if (! $account->appAccesses()->exists()) {
                $account->roleHistory()->delete();
                $account->delete();
            }
        }

        $createdOrUpdatedAccounts = collect($payload['accounts'])
            ->map(function (array $account) use ($appCode, $defaultPassword, $today) {
                $user = User::query()->firstOrNew(['email' => $account['email']]);

                $user->forceFill([
                    'full_name' => $account['full_name'],
                    'account_type' => $account['account_type'],
                    'role_code' => $account['role_code'],
                    'rig_code' => $account['rig_code'],
                    'active_status' => true,
                    'description' => $account['description'],
                    'must_change_password' => false,
                    'name_confirmed_at' => $user->name_confirmed_at ?? now(),
                ]);

                if (! $user->exists) {
                    $user->password = Hash::make($defaultPassword);
                }

                $user->save();

                AccountAppAccess::query()->updateOrCreate(
                    [
                        'account_id' => $user->id,
                        'app_code' => $appCode,
                    ],
                    ['is_active' => true]
                );

                if ($account['rig_code'] && $account['role_code']) {
                    $schedule = $user->roleHistory()
                        ->activeOn($today)
                        ->latest('effective_from')
                        ->first();

                    if ($schedule) {
                        $schedule->forceFill([
                            'role_code' => $account['role_code'],
                            'rig_code' => $account['rig_code'],
                            'active_status' => true,
                            'remarks' => $schedule->remarks ?: 'Imported from User List.xlsx',
                            'person_name' => $schedule->person_name ?: $account['current_holder_name'],
                        ])->save();
                    } else {
                        $user->roleHistory()->create([
                            'person_name' => $account['current_holder_name'],
                            'role_code' => $account['role_code'],
                            'rig_code' => $account['rig_code'],
                            'effective_from' => $today,
                            'effective_to' => null,
                            'remarks' => 'Imported from User List.xlsx',
                            'active_status' => true,
                        ]);
                    }
                }

                return $user->fresh(['appAccesses', 'roleHistory']);
            });

        return [
            'rigs_count' => $rigs->count(),
            'accounts_count' => $createdOrUpdatedAccounts->count(),
        ];
    }

    public function parseWorkbook(string $path): array
    {
        if (blank($path) || ! file_exists($path)) {
            throw new InvalidArgumentException("User list workbook not found: {$path}");
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $entries = [];
        $currentRigName = null;

        foreach ($rows as $index => $row) {
            if ($index === 1) {
                continue;
            }

            $designation = trim((string) ($row['B'] ?? ''));
            $email = Str::lower(trim((string) ($row['C'] ?? '')));
            $rigCell = trim((string) ($row['D'] ?? ''));

            if ($rigCell !== '') {
                $currentRigName = $rigCell;
            }

            if ($designation === '' || $email === '') {
                continue;
            }

            if ($currentRigName === null) {
                throw new InvalidArgumentException("Rig name missing before row {$index} in workbook: {$path}");
            }

            $roleCode = $this->mapDesignationToRole($designation);

            if ($roleCode === null) {
                continue;
            }

            $rigCode = $this->generateRigCode($currentRigName);

            $entries[] = [
                'designation' => $designation,
                'email' => $email,
                'rig_name' => $currentRigName,
                'rig_code' => $rigCode,
                'role_code' => $roleCode,
                'full_name' => sprintf('%s %s', $currentRigName, $roleCode),
                'current_holder_name' => sprintf('%s %s Holder', $currentRigName, $roleCode),
                'account_type' => 'shared_role',
                'description' => sprintf('Imported from User List.xlsx (%s)', $designation),
            ];
        }

        $rigs = collect($entries)
            ->groupBy('rig_code')
            ->map(function (Collection $group, string $rigCode) {
                $first = $group->first();

                return [
                    'code' => $rigCode,
                    'name' => $first['rig_name'],
                    'location' => config('er_drill.default_rig_location'),
                    'timezone' => config('er_drill.default_timezone'),
                ];
            })
            ->values()
            ->all();

        $accounts = [
            [
                'email' => 'admin@vantris.com',
                'full_name' => 'Vantris Administrator',
                'role_code' => UserRole::Administrator->value,
                'rig_code' => null,
                'account_type' => 'admin',
                'current_holder_name' => 'Vantris Administrator',
                'description' => 'Shared ER Drill administrator account',
            ],
            ...$entries,
        ];

        return [
            'rigs' => $rigs,
            'accounts' => $accounts,
        ];
    }

    protected function mapDesignationToRole(string $designation): ?string
    {
        return match (Str::upper(trim($designation))) {
            'OIM' => UserRole::OIM->value,
            'BE' => UserRole::BE->value,
            'STO' => UserRole::STO->value,
            default => null,
        };
    }

    protected function generateRigCode(string $rigName): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', Str::upper($rigName)) ?: 'RIG';

        return Str::substr($normalized, 0, 3);
    }
}
