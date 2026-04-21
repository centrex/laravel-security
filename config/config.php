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

];
