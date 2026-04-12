<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Security Console" subtitle="Operational view of flags, anomalous activity, approvals, and IP reputation." icon="o-shield-check" />

        @include('security::partials.flash')
        @include('security::partials.nav')

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-security::stat-card
                    :title="$stat['title']"
                    :value="$stat['value']"
                    :description="$stat['description']"
                    :icon="$stat['icon']"
                    :route="$stat['route']"
                />
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <x-tallui-card title="Latest Risk Flags" subtitle="Most recent incidents raised by the detection layer." icon="o-shield-exclamation">
                <div class="space-y-3">
                    @forelse ($latestFlags as $flag)
                        <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-base-200 px-4 py-3">
                            <div>
                                <a href="{{ route('security.risk-flags.review', $flag) }}" class="font-medium link link-hover">
                                    {{ str($flag->flag_type)->headline() }}
                                </a>
                                <div class="text-sm text-base-content/60">{{ $flag->reason }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-security::status-badge :value="$flag->severity" mode="severity" />
                                <x-security::status-badge :value="$flag->resolved_at ? 'resolved' : 'open'" />
                            </div>
                        </div>
                    @empty
                        <x-tallui-empty-state title="No flags raised" description="New security incidents will appear here." />
                    @endforelse
                </div>
            </x-tallui-card>

            <x-tallui-card title="IP Reputation" subtitle="Quick summary of the network posture." icon="o-globe-alt">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-security::stat-card title="Suspicious" :value="$ipOverview['suspicious']" description="IPs marked for closer review." icon="o-eye" compact />
                    <x-security::stat-card title="Blocklist" :value="$ipOverview['blocklist']" description="Blocked or denied sources." icon="o-no-symbol" compact />
                    <x-security::stat-card title="High Risk" :value="$ipOverview['high_risk']" description="IPs scoring 70 or above." icon="o-fire" compact />
                </div>
                <div class="mt-4">
                    <x-tallui-button :link="route('security.ip-lists.index')" class="btn-outline btn-sm">Open IP Intelligence</x-tallui-button>
                </div>
            </x-tallui-card>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-tallui-card title="Anomalous Activity" subtitle="Recent events sorted by detection time." icon="o-bug-ant">
                <div class="space-y-3">
                    @forelse ($latestActivities as $activity)
                        <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-base-200 px-4 py-3">
                            <div>
                                <div class="font-medium">{{ str($activity->event_type)->headline() }}</div>
                                <div class="text-sm text-base-content/60">
                                    {{ $activity->user?->name ?? 'System' }}
                                    @if ($activity->ip_address)
                                        · {{ $activity->ip_address }}
                                    @endif
                                    @if ($activity->endpoint)
                                        · {{ $activity->endpoint }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-security::status-badge :value="$activity->is_anomalous ? 'anomalous' : 'normal'" mode="activity" />
                                <span class="text-sm text-base-content/60">Risk {{ $activity->risk_score }}</span>
                            </div>
                        </div>
                    @empty
                        <x-tallui-empty-state title="No security activity" description="No activity logs have been recorded yet." />
                    @endforelse
                </div>
            </x-tallui-card>

            <x-tallui-card title="Pending Approvals" subtitle="Actions blocked until a second approver confirms them." icon="o-check-badge">
                <div class="space-y-3">
                    @forelse ($pendingApprovals as $approval)
                        <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-base-200 px-4 py-3">
                            <div>
                                <div class="font-medium">{{ str($approval->action_type)->headline() }}</div>
                                <div class="text-sm text-base-content/60">
                                    Requested by {{ $approvalUsers->get($approval->requested_by, 'User #'.$approval->requested_by) }}
                                </div>
                                @if ($approval->reason)
                                    <div class="mt-1 text-sm text-base-content/70">{{ $approval->reason }}</div>
                                @endif
                            </div>
                            <x-security::status-badge :value="$approval->status" />
                        </div>
                    @empty
                        <x-tallui-empty-state title="No pending approvals" description="Approval workflows are currently clear." />
                    @endforelse
                </div>
            </x-tallui-card>
        </div>
    </div>
</x-layouts::app>
