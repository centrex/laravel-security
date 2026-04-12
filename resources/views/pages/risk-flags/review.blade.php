<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Risk Flag Review" subtitle="Inspect evidence, related activity, and resolution state." icon="o-shield-exclamation">
            <x-slot:actions>
                <x-tallui-button :link="route('security.risk-flags.index')" class="btn-outline btn-sm">Back to Flags</x-tallui-button>
            </x-slot:actions>
        </x-tallui-page-header>

        @include('security::partials.flash')
        @include('security::partials.nav')

        <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <x-tallui-card title="Incident Summary" subtitle="Primary event metadata and current status." icon="o-bug-ant">
                <dl class="grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-base-content/50">Type</dt>
                        <dd class="mt-1 font-medium">{{ str($flag->flag_type)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-base-content/50">Severity</dt>
                        <dd class="mt-1"><x-security::status-badge :value="$flag->severity" mode="severity" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-base-content/50">Status</dt>
                        <dd class="mt-1"><x-security::status-badge :value="$flag->resolved_at ? 'resolved' : 'open'" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-base-content/50">Detected</dt>
                        <dd class="mt-1">{{ $flag->created_at?->format('M d, Y H:i') }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs uppercase text-base-content/50">Reason</dt>
                        <dd class="mt-1 text-sm text-base-content/70">{{ $flag->reason }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs uppercase text-base-content/50">User</dt>
                        <dd class="mt-1">
                            @if ($flag->user_id)
                                <a href="{{ route('security.users.timeline', $flag->user_id) }}" class="link link-hover font-medium">
                                    {{ $flag->user?->name ?? ('User #'.$flag->user_id) }}
                                </a>
                            @else
                                System
                            @endif
                        </dd>
                    </div>
                </dl>

                @if (! $flag->resolved_at)
                    <form method="POST" action="{{ route('security.risk-flags.resolve', $flag) }}" class="mt-6">
                        @csrf
                        @method('PATCH')
                        <x-tallui-button type="submit" class="btn-primary btn-sm">Resolve Flag</x-tallui-button>
                    </form>
                @endif
            </x-tallui-card>

            <x-tallui-card title="Evidence" subtitle="Raw evidence captured for the incident." icon="o-document-text">
                <pre class="overflow-x-auto rounded-2xl bg-base-200/60 p-4 text-xs">{{ json_encode($flag->evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </x-tallui-card>
        </div>

        <x-tallui-card title="Related Activity" subtitle="Most recent events for this user context." icon="o-clock">
            <div class="space-y-3">
                @forelse ($relatedActivities as $activity)
                    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-base-200 px-4 py-3">
                        <div>
                            <div class="font-medium">{{ str($activity->event_type)->headline() }}</div>
                            <div class="text-sm text-base-content/60">
                                {{ $activity->endpoint ?: 'No endpoint captured' }}
                                @if ($activity->ip_address)
                                    · {{ $activity->ip_address }}
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-security::status-badge :value="$activity->is_anomalous ? 'anomalous' : 'normal'" mode="activity" />
                            <span class="text-sm text-base-content/60">Risk {{ $activity->risk_score }}</span>
                        </div>
                    </div>
                @empty
                    <x-tallui-empty-state title="No related activity" description="No recent activity logs were found for this user context." />
                @endforelse
            </div>
        </x-tallui-card>
    </div>
</x-layouts::app>
