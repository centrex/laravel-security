<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="Security Approvals" subtitle="Review and confirm four-eyes security actions." icon="o-check-badge" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <x-tallui-card>
            <form method="GET" action="{{ route('security.approvals.index') }}" class="flex flex-wrap items-end gap-4">
                <label class="form-control w-full md:w-64">
                    <span class="label-text text-xs uppercase text-base-content/60">Status</span>
                    <select name="status" class="select select-bordered">
                        <option value="">All</option>
                        @foreach (['pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <x-tallui-button type="submit" class="btn-primary btn-sm">Apply Filters</x-tallui-button>
                <x-tallui-button :link="route('security.approvals.index')" class="btn-ghost btn-sm">Reset</x-tallui-button>
            </form>
        </x-tallui-card>

        <div class="space-y-4">
            @forelse ($approvals as $approval)
                <x-tallui-card>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="text-lg font-semibold">{{ str($approval->action_type)->headline() }}</div>
                                <x-security::status-badge :value="$approval->status" />
                            </div>
                            <div class="text-sm text-base-content/60">
                                Target #{{ $approval->target_id }}
                                · Requested by {{ $userNames->get($approval->requested_by, 'User #'.$approval->requested_by) }}
                                @if ($approval->approved_by)
                                    · Approved by {{ $userNames->get($approval->approved_by, 'User #'.$approval->approved_by) }}
                                @endif
                            </div>
                            @if ($approval->reason)
                                <div class="text-sm text-base-content/70">{{ $approval->reason }}</div>
                            @endif
                        </div>

                        @if ($approval->status === 'pending')
                            <form method="POST" action="{{ route('security.approvals.approve', $approval) }}">
                                @csrf
                                @method('PATCH')
                                <x-tallui-button type="submit" class="btn-primary btn-sm">Approve</x-tallui-button>
                            </form>
                        @endif
                    </div>
                </x-tallui-card>
            @empty
                <x-tallui-empty-state title="No approvals found" description="Security approvals matching these filters will appear here." />
            @endforelse
        </div>

        <div>{{ $approvals->links() }}</div>
    </div>
</x-layouts::app>
