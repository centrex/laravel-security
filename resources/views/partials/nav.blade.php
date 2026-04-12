<div class="flex flex-wrap gap-2">
    <x-tallui-button :link="route('security.dashboard')" class="{{ request()->routeIs('security.dashboard') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Overview</x-tallui-button>
    <x-tallui-button :link="route('security.risk-flags.index')" class="{{ request()->routeIs('security.risk-flags.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Risk Flags</x-tallui-button>
    <x-tallui-button :link="route('security.activities.index')" class="{{ request()->routeIs('security.activities.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Activity</x-tallui-button>
    <x-tallui-button :link="route('security.approvals.index')" class="{{ request()->routeIs('security.approvals.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Approvals</x-tallui-button>
    <x-tallui-button :link="route('security.ip-lists.index')" class="{{ request()->routeIs('security.ip-lists.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">IP Intelligence</x-tallui-button>
</div>
