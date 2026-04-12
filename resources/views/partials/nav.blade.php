<div class="flex flex-wrap gap-2">
    <x-tallui-button :link="route('security.dashboard')" class="{{ request()->routeIs('security.dashboard') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Overview</x-tallui-button>
    <x-tallui-button :link="route('security.risk-flags.index')" class="{{ request()->routeIs('security.risk-flags.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Risk Flags</x-tallui-button>
    <x-tallui-button :link="route('security.activities.index')" class="{{ request()->routeIs('security.activities.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Activity</x-tallui-button>
    <x-tallui-button :link="route('security.approvals.index')" class="{{ request()->routeIs('security.approvals.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Approvals</x-tallui-button>
    <x-tallui-button :link="route('security.ip-lists.index')" class="{{ request()->routeIs('security.ip-lists.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">IP Intelligence</x-tallui-button>
    @php($securityNavUser = auth()->user())
    @if (
        ($securityNavUser?->can('security.roles.manage') ?? false)
        || ($securityNavUser?->can('security.permissions.manage') ?? false)
        || (is_object($securityNavUser) && method_exists($securityNavUser, 'hasAnyRole') && $securityNavUser->hasAnyRole(['security_admin', 'admin']))
        || (($securityNavUser?->is_admin ?? false) === true)
    )
        <x-tallui-button :link="route('security.roles.index')" class="{{ request()->routeIs('security.roles.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Roles</x-tallui-button>
        <x-tallui-button :link="route('security.permissions.index')" class="{{ request()->routeIs('security.permissions.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Permissions</x-tallui-button>
    @endif
</div>
