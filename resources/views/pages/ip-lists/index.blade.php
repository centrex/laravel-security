<x-layouts::app>
    <div class="space-y-6">
        <x-tallui-page-header title="IP Intelligence" subtitle="Track blocklists, suspicious traffic, and geo/network reputation." icon="o-globe-alt" />
        @include('security::partials.flash')
        @include('security::partials.nav')

        <x-tallui-card>
            <form method="GET" action="{{ route('security.ip-lists.index') }}" class="grid gap-4 md:grid-cols-4">
                <label class="form-control">
                    <span class="label-text text-xs uppercase text-base-content/60">Status</span>
                    <select name="status" class="select select-bordered">
                        <option value="">All</option>
                        @foreach (['whitelist', 'suspicious', 'blocklist'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs uppercase text-base-content/60">Search</span>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="IP, country, ISP, or remarks" class="input input-bordered w-full" />
                </label>

                <div class="flex items-end gap-3">
                    <x-tallui-button type="submit" class="btn-primary btn-sm">Apply Filters</x-tallui-button>
                    <x-tallui-button :link="route('security.ip-lists.index')" class="btn-ghost btn-sm">Reset</x-tallui-button>
                </div>
            </form>
        </x-tallui-card>

        <x-tallui-card padding="none">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="bg-base-50 text-xs uppercase text-base-content/50">
                            <th class="pl-5">IP</th>
                            <th>Status</th>
                            <th>Threat</th>
                            <th>Risk</th>
                            <th>Location</th>
                            <th>Provider</th>
                            <th class="pr-5">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse ($ips as $ip)
                            <tr class="hover:bg-base-50">
                                <td class="pl-5 font-medium">{{ $ip->ip_address }}</td>
                                <td><x-security::status-badge :value="$ip->status" mode="ip" /></td>
                                <td class="text-sm text-base-content/70">{{ $ip->threat_type ?: 'n/a' }}</td>
                                <td>{{ $ip->risk_score }}</td>
                                <td class="text-sm text-base-content/70">{{ trim(($ip->country_code ?: '').' '.($ip->asn ?: '')) ?: 'n/a' }}</td>
                                <td class="text-sm text-base-content/70">{{ $ip->isp ?: 'n/a' }}</td>
                                <td class="pr-5 text-sm text-base-content/70">{{ $ip->remarks ?: 'n/a' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-tallui-empty-state title="No IP records found" description="No IP intelligence records matched the current filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-base-200 px-5 py-3">{{ $ips->links() }}</div>
        </x-tallui-card>
    </div>
</x-layouts::app>
