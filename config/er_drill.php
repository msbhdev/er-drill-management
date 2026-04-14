<?php

return [
    'default_timezone' => env('ER_DRILL_DEFAULT_TIMEZONE', 'Asia/Kuala_Lumpur'),
    'timezones' => [
        'Asia/Kuala_Lumpur' => 'Kuala Lumpur (UTC+8)',
        'Asia/Bangkok' => 'Bangkok (UTC+7)',
        'UTC' => 'UTC',
    ],
    'roles' => [
        'STO' => 'STO',
        'BE' => 'BE',
        'OIM' => 'OIM',
        'RM' => 'RM',
        'Management' => 'Management',
        'Administrator' => 'Administrator',
    ],
    'drill_statuses' => [
        'draft' => 'draft',
        'submitted' => 'submitted',
        'returned_by_be' => 'returned_by_be',
        'verified' => 'verified',
        'returned_by_oim' => 'returned_by_oim',
        'approved' => 'approved',
        'closed' => 'closed',
    ],
    'action_statuses' => [
        'open' => 'open',
        'in_progress' => 'in_progress',
        'closed' => 'closed',
    ],
];
