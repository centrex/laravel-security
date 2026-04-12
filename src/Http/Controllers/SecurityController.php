<?php

declare(strict_types = 1);

namespace Centrex\Security\Http\Controllers;

use Centrex\Security\Models\{IpList, SecurityActivityLog, SecurityApproval, SecurityRiskFlag};
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
}
