<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Database Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Available database drivers
    |
    */
    
    'drivers' => [
        'database' => [
            'connection' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Scoring Configuration
    |--------------------------------------------------------------------------
    | Define how risk scores are calculated based on various factors.
    |
    */
    'risk_scoring' => [
        'base_scores' => [
            'suspicious_ip' => 30,
            'blocked_ip' => 70,
            'anomalous_activity' => 40,
            'phishing_flag' => 50,
            'impossible_travel_flag' => 60,
            'device_change_flag' => 40,
        ],
        'thresholds' => [
            'low' => 0,
            'medium' => 30,
            'high' => 60,   
            'critical' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    | Optional prefix for all security-related tables to avoid naming conflicts.
    |   Default is 'sec_' resulting in tables like 'sec_ip_lists', 'sec_security_activity_logs', etc.
    */
    'table_prefix' => 'sec_',

    /*
    |--------------------------------------------------------------------------
    | Access Management (Roles & Permissions)
    |--------------------------------------------------------------------------
    | SecurityController::roles()/permissions() manage whatever role/permission models
    | you point this at — the host app owns its own authorization system, this package
    | doesn't ship one. Any model works as long as Role exposes a `permissions()`
    | *ToMany relation and Permission exposes a `roles()` *ToMany relation (both
    | spatie/laravel-permission's and jurager/teams's models satisfy this).
    |
    | 'identifier_column' is which column role/permission pickers and unique/exists
    | validation match against — spatie/laravel-permission identifies permissions by
    | `name`; jurager/teams identifies them by `code` (its `name` is optional/display-only).
    |
    | 'role_defaults'/'permission_defaults' are extra attributes merged in when the
    | security UI creates a new row — e.g. jurager/teams roles/permissions belong to a
    | team (`team_id` is required), which spatie's models have no equivalent of. Accepts
    | a plain array or a closure (called with no arguments) for values only known at
    | request time, e.g.:
    |   'role_defaults' => fn () => ['team_id' => auth()->user()?->currentTeam()?->id],
    */
    'access_management' => [
        'models' => [
            'role' => env('SECURITY_ROLE_MODEL', \Spatie\Permission\Models\Role::class),
            'permission' => env('SECURITY_PERMISSION_MODEL', \Spatie\Permission\Models\Permission::class),
        ],
        'identifier_column' => env('SECURITY_PERMISSION_IDENTIFIER_COLUMN', 'name'),
        'role_defaults' => null,
        'permission_defaults' => null,
    ],

];
