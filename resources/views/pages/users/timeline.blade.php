<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="User Security Timeline" :subtitle="'Activity and flags for user #'.$userId" icon="o-clock" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <div class="grid gap-6 xl:grid-cols-2">
            <x-tallui-card title="Recent Activity" subtitle="Latest recorded activity entries." icon="o-bug-ant">
                <div class="space-y-3">
                    @forelse ($activities as $activity)
                        <div class="rounded-2xl border border-base-200 px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="font-medium">{{ str($activity->event_type)->headline() }}</div>
                                <x-security::status-badge :value="$activity->is_anomalous ? 'anomalous' : 'normal'" mode="activity" />
                            </div>
                            <div class="mt-1 text-sm text-base-content/60">
                                {{ $activity->created_at?->format('M d, Y H:i') }}
                                @if ($activity->ip_address)
                                    · {{ $activity->ip_address }}
                                @endif
                                @if ($activity->endpoint)
                                    · {{ $activity->endpoint }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-tallui-empty-state title="No activity found" description="There are no activity logs for this user." />
                    @endforelse
                </div>
            </x-tallui-card>

            <x-tallui-card title="Risk Flags" subtitle="Flags raised against this user context." icon="o-shield-exclamation">
                <div class="space-y-3">
                    @forelse ($flags as $flag)
                        <div class="rounded-2xl border border-base-200 px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <a href="{{ route('security.risk-flags.review', $flag) }}" class="font-medium link link-hover">
                                    {{ str($flag->flag_type)->headline() }}
                                </a>
                                <div class="flex items-center gap-2">
                                    <x-security::status-badge :value="$flag->severity" mode="severity" />
                                    <x-security::status-badge :value="$flag->resolved_at ? 'resolved' : 'open'" />
                                </div>
                            </div>
                            <div class="mt-1 text-sm text-base-content/70">{{ $flag->reason }}</div>
                        </div>
                    @empty
                        <x-tallui-empty-state title="No flags found" description="There are no risk flags for this user." />
                    @endforelse
                </div>
            </x-tallui-card>
        </div>
    </div>
</x-layouts::app>
