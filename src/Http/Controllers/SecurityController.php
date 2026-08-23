<?php

declare(strict_types = 1);

namespace Centrex\Security\Http\Controllers;

use Centrex\Security\Models\{IpList, SecurityActivityLog, SecurityApproval, SecurityRiskFlag};
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class SecurityController extends Controller
{
    public function dashboard(): View
    {
        $this->authorizeView();

        $stats = [
            [
                'title'       => 'Open Flags',
                'value'       => (string) SecurityRiskFlag::query()->open()->count(),
                'description' => 'Incidents waiting for review or resolution.',
                'icon'        => 'o-shield-exclamation',
                'route'       => route('security.risk-flags.index', ['status' => 'open']),
            ],
            [
                'title'       => 'Critical Flags',
                'value'       => (string) SecurityRiskFlag::query()->open()->critical()->count(),
                'description' => 'Highest-severity events requiring fast handling.',
                'icon'        => 'o-fire',
                'route'       => route('security.risk-flags.index', ['status' => 'open', 'severity' => 'critical']),
            ],
            [
                'title'       => 'Anomalous Events',
                'value'       => (string) SecurityActivityLog::query()->anomalous()->count(),
                'description' => 'Activity log entries marked as abnormal.',
                'icon'        => 'o-bug-ant',
                'route'       => route('security.activities.index', ['anomalous' => 1]),
            ],
            [
                'title'       => 'Pending Approvals',
                'value'       => (string) SecurityApproval::query()->where('status', 'pending')->count(),
                'description' => '4-eyes actions waiting for a second approver.',
                'icon'        => 'o-check-badge',
                'route'       => route('security.approvals.index', ['status' => 'pending']),
            ],
            [
                'title'       => 'Access Roles',
                'value'       => (string) $this->roleModelClass()::query()->count(),
                'description' => 'Configured application roles available for assignment.',
                'icon'        => 'o-users',
                'route'       => route('security.roles.index'),
            ],
            [
                'title'       => 'Permissions',
                'value'       => (string) $this->permissionModelClass()::query()->count(),
                'description' => 'Granular permission keys available for assignment.',
                'icon'        => 'o-key',
                'route'       => route('security.permissions.index'),
            ],
        ];

        return view('security::pages.dashboard', [
            'stats'            => $stats,
            'latestFlags'      => SecurityRiskFlag::query()->with('user')->latest()->limit(6)->get(),
            'latestActivities' => SecurityActivityLog::query()->with('user')->latest()->limit(8)->get(),
            'pendingApprovals' => SecurityApproval::query()->where('status', 'pending')->latest()->limit(6)->get(),
            'ipOverview'       => [
                'suspicious' => IpList::query()->where('status', 'suspicious')->count(),
                'blocklist'  => IpList::query()->where('status', 'blocklist')->count(),
                'high_risk'  => IpList::query()->where('risk_score', '>=', 70)->count(),
            ],
            'approvalUsers' => $this->resolveUserNames(
                SecurityApproval::query()
                    ->latest()
                    ->limit(6)
                    ->get(['requested_by', 'approved_by'])
                    ->flatMap(fn (SecurityApproval $approval): array => array_filter([
                        $approval->requested_by,
                        $approval->approved_by,
                    ]))
                    ->unique()
                    ->values(),
            ),
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorizeView();

        $query = SecurityRiskFlag::query()
            ->with('user')
            ->latest();

        if ($request->string('status')->toString() === 'open') {
            $query->whereNull('resolved_at');
        }

        if ($request->string('status')->toString() === 'resolved') {
            $query->whereNotNull('resolved_at');
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('flag_type', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        return view('security::pages.risk-flags.index', [
            'flags'         => $query->paginate(20)->withQueryString(),
            'filters'       => $request->only(['status', 'severity', 'search']),
            'openCount'     => SecurityRiskFlag::query()->open()->count(),
            'criticalCount' => SecurityRiskFlag::query()->open()->critical()->count(),
        ]);
    }

    public function show(SecurityRiskFlag $riskFlag): View
    {
        $this->authorizeView();

        $riskFlag->loadMissing('user');

        $relatedActivities = SecurityActivityLog::query()
            ->with('user')
            ->when($riskFlag->user_id, fn (Builder $query): Builder => $query->where('user_id', $riskFlag->user_id))
            ->latest()
            ->limit(8)
            ->get();

        return view('security::pages.risk-flags.review', [
            'flag'              => $riskFlag,
            'relatedActivities' => $relatedActivities,
        ]);
    }

    public function resolve(SecurityRiskFlag $riskFlag): RedirectResponse
    {
        $this->authorizeResolve();

        $riskFlag->resolve();

        return redirect()
            ->route('security.risk-flags.review', $riskFlag)
            ->with('security_success', 'Risk flag resolved.');
    }

    public function activities(Request $request): View
    {
        $this->authorizeView();

        $query = SecurityActivityLog::query()
            ->with('user')
            ->latest();

        if ($request->boolean('anomalous')) {
            $query->where('is_anomalous', true);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('endpoint', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        return view('security::pages.activities.index', [
            'activities' => $query->paginate(25)->withQueryString(),
            'filters'    => $request->only(['anomalous', 'event_type', 'search']),
            'eventTypes' => SecurityActivityLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type'),
        ]);
    }

    public function approvals(Request $request): View
    {
        $this->authorizeView();

        $query = SecurityApproval::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $approvals = $query->paginate(20)->withQueryString();

        return view('security::pages.approvals.index', [
            'approvals' => $approvals,
            'filters'   => $request->only(['status']),
            'userNames' => $this->resolveUserNames(
                $approvals->getCollection()
                    ->flatMap(fn (SecurityApproval $approval): array => array_filter([
                        $approval->requested_by,
                        $approval->approved_by,
                    ]))
                    ->unique()
                    ->values(),
            ),
        ]);
    }

    public function approve(SecurityApproval $approval): RedirectResponse
    {
        $this->authorizeResolve();

        $approval->approve((int) auth()->id());

        return redirect()
            ->route('security.approvals.index')
            ->with('security_success', 'Security approval confirmed.');
    }

    public function ipLists(Request $request): View
    {
        $this->authorizeView();

        $query = IpList::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%")
                    ->orWhere('isp', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        return view('security::pages.ip-lists.index', [
            'ips'     => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function roles(): View
    {
        $this->authorizeAccessManagement();

        $roleModel = $this->roleModelClass();
        $permissionModel = $this->permissionModelClass();

        return view('security::pages.roles.index', [
            'roles' => $roleModel::query()
                ->with('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'permissions'      => $permissionModel::query()->orderBy('name')->get(),
            'identifierColumn' => $this->identifierColumn(),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->authorizeAccessManagement();

        $identifierColumn = $this->identifierColumn();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:' . $this->roleTable() . ',name'],
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:' . $this->permissionTable() . ',' . $identifierColumn],
        ]);

        $roleModel = $this->roleModelClass();
        $role = $roleModel::query()->create($this->withGuardName([
            'name' => $validated['name'],
        ], $roleModel, $this->roleDefaults()));

        $this->syncPermissions($role, $validated['permissions'] ?? [], $identifierColumn);

        return redirect()
            ->route('security.roles.index')
            ->with('security_success', 'Role created.');
    }

    public function updateRole(Request $request, int|string $roleId): RedirectResponse
    {
        $this->authorizeAccessManagement();

        $roleModel = $this->roleModelClass();
        $role = $roleModel::query()->findOrFail($roleId);
        $identifierColumn = $this->identifierColumn();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:' . $this->roleTable() . ',name,' . $role->getKey()],
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:' . $this->permissionTable() . ',' . $identifierColumn],
        ]);

        $role->update(['name' => $validated['name']]);
        $this->syncPermissions($role, $validated['permissions'] ?? [], $identifierColumn);

        return redirect()
            ->route('security.roles.index')
            ->with('security_success', 'Role updated.');
    }

    public function permissions(): View
    {
        $this->authorizeAccessManagement();

        $permissionModel = $this->permissionModelClass();
        $roleModel = $this->roleModelClass();

        return view('security::pages.permissions.index', [
            'permissions' => $permissionModel::query()
                ->with('roles')
                ->orderBy('name')
                ->get(),
            'roles'            => $roleModel::query()->orderBy('name')->get(),
            'identifierColumn' => $this->identifierColumn(),
        ]);
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $this->authorizeAccessManagement();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:' . $this->permissionTable() . ',name'],
        ]);

        $permissionModel = $this->permissionModelClass();
        $permissionModel::query()->create($this->withGuardName([
            'name' => $validated['name'],
        ], $permissionModel, $this->permissionDefaults()));

        return redirect()
            ->route('security.permissions.index')
            ->with('security_success', 'Permission created.');
    }

    public function updatePermission(Request $request, int|string $permissionId): RedirectResponse
    {
        $this->authorizeAccessManagement();

        $permissionModel = $this->permissionModelClass();
        $permission = $permissionModel::query()->findOrFail($permissionId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:' . $this->permissionTable() . ',name,' . $permission->getKey()],
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()
            ->route('security.permissions.index')
            ->with('security_success', 'Permission updated.');
    }

    public function timeline(int $userId): View
    {
        $this->authorizeView();

        return view('security::pages.users.timeline', [
            'userId'     => $userId,
            'activities' => SecurityActivityLog::query()->where('user_id', $userId)->latest()->limit(25)->get(),
            'flags'      => SecurityRiskFlag::query()->where('user_id', $userId)->latest()->limit(15)->get(),
        ]);
    }

    private function authorizeView(): void
    {
        abort_unless(auth()->user()?->can('security.risk-flags.view'), 403);
    }

    private function authorizeResolve(): void
    {
        abort_unless(auth()->user()?->can('security.risk-flags.resolve'), 403);
    }

    private function authorizeAccessManagement(): void
    {
        $user = auth()->user();

        abort_unless(
            $user !== null
            && (
                $user->can('security.roles.manage')
                || $user->can('security.permissions.manage')
                || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['security_admin', 'admin']))
                || (bool) ($user->is_admin ?? false)
            ),
            403,
        );
    }

    private function resolveUserNames(Collection $userIds): Collection
    {
        $userIds = $userIds
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        if (!class_exists($userModel)) {
            return collect();
        }

        return $userModel::query()
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');
    }

    private function roleModelClass(): string
    {
        return config('security.access_management.models.role', \Spatie\Permission\Models\Role::class);
    }

    private function permissionModelClass(): string
    {
        return config('security.access_management.models.permission', \Spatie\Permission\Models\Permission::class);
    }

    private function roleTable(): string
    {
        $model = $this->roleModelClass();

        return (new $model)->getTable();
    }

    private function permissionTable(): string
    {
        $model = $this->permissionModelClass();

        return (new $model)->getTable();
    }

    /** Which column role/permission pickers and unique/exists validation match against — see config('security.access_management.identifier_column'). */
    private function identifierColumn(): string
    {
        return (string) config('security.access_management.identifier_column', 'name');
    }

    private function roleDefaults(): array
    {
        return $this->resolveDefaults('access_management.role_defaults');
    }

    private function permissionDefaults(): array
    {
        return $this->resolveDefaults('access_management.permission_defaults');
    }

    private function resolveDefaults(string $configKey): array
    {
        $value = config('security.' . $configKey);

        if (is_callable($value)) {
            $value = $value();
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Merges configured defaults (e.g. jurager/teams's required team_id, which
     * spatie/laravel-permission's models have no equivalent of) onto $attributes, and adds
     * guard_name only when the target model actually has that column — spatie's models
     * need it, jurager/teams's don't, and including it unconditionally would either be
     * silently dropped or throw under strict mass-assignment depending on the host app's
     * Model configuration.
     */
    private function withGuardName(array $attributes, string $modelClass, array $defaults): array
    {
        $attributes = array_merge($defaults, $attributes);

        if (in_array('guard_name', (new $modelClass)->getFillable(), true)) {
            $attributes['guard_name'] = config('auth.defaults.guard', 'web');
        }

        return $attributes;
    }

    /**
     * Model-agnostic replacement for Spatie's Role::syncPermissions() (a HasRoles-trait
     * method jurager/teams's Role doesn't have): resolves the submitted permission
     * identifiers (whichever column $identifierColumn points at) to IDs via the configured
     * permission model, then syncs the role's own permissions() relation directly. Both
     * spatie/laravel-permission's Role and jurager/teams's Role expose a `permissions()`
     * *ToMany relation, so this works for either without knowing which one is configured.
     */
    private function syncPermissions(Model $role, array $identifiers, string $identifierColumn): void
    {
        if (!method_exists($role, 'permissions')) {
            return;
        }

        if ($identifiers === []) {
            $role->permissions()->sync([]);

            return;
        }

        $permissionModel = $this->permissionModelClass();
        $ids = $permissionModel::query()->whereIn($identifierColumn, $identifiers)->pluck('id');

        $role->permissions()->sync($ids);
    }
}
