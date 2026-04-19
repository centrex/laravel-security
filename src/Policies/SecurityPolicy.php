<?php

declare(strict_types = 1);

namespace Centrex\Security\Policies;

final class SecurityPolicy
{
    public function viewRisks(mixed $user): bool
    {
        return $this->hasAnyRole($user, ['security', 'security_admin', 'admin'])
            || $this->isAdmin($user)
            || $this->isTeamOwner($user);
    }

    public function resolveRisk(mixed $user): bool
    {
        return $this->hasAnyRole($user, ['security', 'security_admin'])
            || $this->isAdmin($user)
            || $this->isTeamOwner($user);
    }

    public function blockUser(mixed $user): bool
    {
        return $this->hasRole($user, 'security_admin')
            || $this->isAdmin($user)
            || $this->isTeamOwner($user);
    }

    private function hasAnyRole(mixed $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($user, $role)) {
                return true;
            }
        }

        return false;
    }

    private function hasRole(mixed $user, string $role): bool
    {
        if (!is_object($user) || !method_exists($user, 'hasRole')) {
            return false;
        }

        return (bool) $user->hasRole($role);
    }

    private function isAdmin(mixed $user): bool
    {
        if (!is_object($user)) {
            return false;
        }

        try {
            return (bool) ($user->is_admin ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    private function isTeamOwner(mixed $user): bool
    {
        if (!is_object($user) || !method_exists($user, 'allTeams') || !method_exists($user, 'ownsTeam')) {
            return false;
        }

        try {
            return $user->allTeams()->contains(fn (mixed $team): bool => $user->ownsTeam($team));
        } catch (\Throwable) {
            return false;
        }
    }
}
