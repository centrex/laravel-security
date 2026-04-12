<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Risk Flags" subtitle="Review, filter, and resolve flagged security events." icon="o-shield-exclamation">
            <x-slot:actions>
                <x-tallui-button :link="route('security.dashboard')" class="btn-outline btn-sm">Dashboard</x-tallui-button>
            </x-slot:actions>
        </x-tallui-page-header>

        @include('security::partials.flash')

        <div class="grid gap-4 md:grid-cols-2">
            <x-security::stat-card
                title="Open Flags"
                :value="$openCount"
                description="Still awaiting investigation or resolution."
                icon="o-shield-exclamation"
                :route="route('security.risk-flags.index', ['status' => 'open'])"
            />
            <x-security::stat-card
                title="Critical Open Flags"
                :value="$criticalCount"
                description="Highest priority incidents currently in queue."
                icon="o-fire"
                :route="route('security.risk-flags.index', ['status' => 'open', 'severity' => 'critical'])"
            />
        </div>

        @include('security::partials.nav')

        <x-tallui-card>
            <form method="GET" action="{{ route('security.risk-flags.index') }}" class="grid gap-4 md:grid-cols-4">
                <label class="form-control">
                    <span class="label-text text-xs uppercase text-base-content/60">Status</span>
                    <select name="status" class="select select-bordered">
                        <option value="">All</option>
                        <option value="open" @selected(($filters['status'] ?? null) === 'open')>Open</option>
                        <option value="resolved" @selected(($filters['status'] ?? null) === 'resolved')>Resolved</option>
                    </select>
                </label>

                <label class="form-control">
                    <span class="label-text text-xs uppercase text-base-content/60">Severity</span>
                    <select name="severity" class="select select-bordered">
                        <option value="">All</option>
                        @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                            <option value="{{ $severity }}" @selected(($filters['severity'] ?? null) === $severity)>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs uppercase text-base-content/60">Search</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Type, reason, or user"
                        class="input input-bordered w-full"
                    />
                </label>

                <div class="md:col-span-4 flex flex-wrap gap-3">
                    <x-tallui-button type="submit" class="btn-primary btn-sm">Apply Filters</x-tallui-button>
                    <x-tallui-button :link="route('security.risk-flags.index')" class="btn-ghost btn-sm">Reset</x-tallui-button>
                </div>
            </form>
        </x-tallui-card>

        <x-tallui-card padding="none">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="bg-base-50 text-xs uppercase text-base-content/50">
                            <th class="pl-5">Type</th>
                            <th>Severity</th>
                            <th>Reason</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Detected</th>
                            <th class="pr-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse ($flags as $flag)
                            <tr class="hover:bg-base-50">
                                <td class="pl-5 font-medium">{{ str($flag->flag_type)->headline() }}</td>
                                <td><x-security::status-badge :value="$flag->severity" mode="severity" /></td>
                                <td class="max-w-sm text-sm text-base-content/70">{{ $flag->reason }}</td>
                                <td>
                                    @if ($flag->user_id)
                                        <a href="{{ route('security.users.timeline', $flag->user_id) }}" class="link link-hover">
                                            {{ $flag->user?->name ?? ('User #'.$flag->user_id) }}
                                        </a>
                                    @else
                                        System
                                    @endif
                                </td>
                                <td><x-security::status-badge :value="$flag->resolved_at ? 'resolved' : 'open'" /></td>
                                <td class="text-sm text-base-content/60">{{ $flag->created_at?->diffForHumans() }}</td>
                                <td class="pr-5 text-right">
                                    <x-tallui-button :link="route('security.risk-flags.review', $flag)" class="btn-ghost btn-xs">Review</x-tallui-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-tallui-empty-state title="No risk flags found" description="No incidents matched the current filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-base-200 px-5 py-3">{{ $flags->links() }}</div>
        </x-tallui-card>
    </div>
</x-layouts::app>
