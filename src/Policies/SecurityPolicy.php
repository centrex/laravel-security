<?php

declare(strict_types = 1);

namespace Centrex\Security\Policies;

final class SecurityPolicy
{
    public function viewRisks(mixed $user): bool
    {
        return $this->hasAnyRole($user, ['security', 'security_admin', 'admin'])
            || $this->isAdmin($user);
    }

    public function resolveRisk(mixed $user): bool
    {
        return $this->hasAnyRole($user, ['security', 'security_admin'])
            || $this->isAdmin($user);
    }

    public function blockUser(mixed $user): bool
    {
        return $this->hasRole($user, 'security_admin') || $this->isAdmin($user);
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

        return (bool) ($user->is_admin ?? false);
    }
}
