@props([
    'value',
    'mode' => 'default',
])

@php
    $normalized = strtolower((string) $value);

    $type = match ($mode) {
        'severity' => match ($normalized) {
            'critical' => 'error',
            'high' => 'warning',
            'medium' => 'info',
            default => 'ghost',
        },
        'activity' => match ($normalized) {
            'anomalous' => 'warning',
            default => 'ghost',
        },
        'ip' => match ($normalized) {
            'blocklist' => 'error',
            'suspicious' => 'warning',
            default => 'success',
        },
        default => match ($normalized) {
            'open', 'pending' => 'warning',
            'resolved', 'approved' => 'success',
            'rejected' => 'error',
            default => 'ghost',
        },
    };
@endphp

<x-tallui-badge :type="$type">{{ str($normalized)->headline() }}</x-tallui-badge>
