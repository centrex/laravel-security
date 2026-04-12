<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Security Activity" subtitle="Inspect event logs, anomaly marks, and risk scores." icon="o-bug-ant" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <x-tallui-card>
            <form method="GET" action="{{ route('security.activities.index') }}" class="grid gap-4 md:grid-cols-4">
                <label class="form-control">
                    <span class="label-text text-xs uppercase text-base-content/60">Event Type</span>
                    <select name="event_type" class="select select-bordered">
                        <option value="">All</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? null) === $eventType)>{{ str($eventType)->headline() }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs uppercase text-base-content/60">Search</span>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="IP, endpoint, event, or user" class="input input-bordered w-full" />
                </label>

                <label class="form-control justify-end">
                    <span class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="anomalous" value="1" class="checkbox checkbox-sm" @checked(! empty($filters['anomalous'])) />
                        <span class="label-text">Anomalous only</span>
                    </span>
                </label>

                <div class="md:col-span-4 flex flex-wrap gap-3">
                    <x-tallui-button type="submit" class="btn-primary btn-sm">Apply Filters</x-tallui-button>
                    <x-tallui-button :link="route('security.activities.index')" class="btn-ghost btn-sm">Reset</x-tallui-button>
                </div>
            </form>
        </x-tallui-card>

        <x-tallui-card padding="none">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="bg-base-50 text-xs uppercase text-base-content/50">
                            <th class="pl-5">Event</th>
                            <th>User</th>
                            <th>Network</th>
                            <th>Endpoint</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th class="pr-5">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse ($activities as $activity)
                            <tr class="hover:bg-base-50">
                                <td class="pl-5 font-medium">{{ str($activity->event_type)->headline() }}</td>
                                <td>
                                    @if ($activity->user_id)
                                        <a href="{{ route('security.users.timeline', $activity->user_id) }}" class="link link-hover">
                                            {{ $activity->user?->name ?? ('User #'.$activity->user_id) }}
                                        </a>
                                    @else
                                        System
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/70">
                                    {{ $activity->ip_address ?: 'n/a' }}
                                    @if ($activity->country_code)
                                        · {{ $activity->country_code }}
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/70">{{ $activity->endpoint ?: 'n/a' }}</td>
                                <td>{{ $activity->risk_score }}</td>
                                <td><x-security::status-badge :value="$activity->is_anomalous ? 'anomalous' : 'normal'" mode="activity" /></td>
                                <td class="pr-5 text-sm text-base-content/60">{{ $activity->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-tallui-empty-state title="No activity found" description="No security activity matched the current filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-base-200 px-5 py-3">{{ $activities->links() }}</div>
        </x-tallui-card>
    </div>
</x-layouts::app>
