@props([
    'title',
    'value',
    'description' => null,
    'icon' => 'o-shield-check',
    'route' => null,
    'compact' => false,
])

<x-tallui-card class="{{ $compact ? 'h-full' : '' }}">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-sm font-medium text-base-content/60">{{ $title }}</div>
            <div class="mt-2 text-3xl font-semibold tracking-tight">{{ $value }}</div>
            @if ($description)
                <div class="mt-2 text-sm text-base-content/65">{{ $description }}</div>
            @endif
        </div>
        <div class="rounded-2xl bg-base-200/70 p-3 text-base-content/70">
            <x-tallui-icon :name="$icon" class="h-6 w-6" />
        </div>
    </div>

    @if ($route)
        <div class="mt-4">
            <a href="{{ $route }}" class="text-sm font-medium text-primary hover:underline">Open</a>
        </div>
    @endif
</x-tallui-card>
