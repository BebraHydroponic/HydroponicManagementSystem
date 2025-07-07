<?php

namespace App\Service;

use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Entity\AppUser;

class RBACService
{
    private PermissionRepository $permissionRepository;
    private RoleRepository $roleRepository;

    public function __construct(
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Returns all permissions in the system.
     *
     * @return array<int, \App\Entity\Permission>
     */
    public function listPermissions(): array
    {
        return $this->permissionRepository->findAll();
    }

    /**
     * Returns all roles in the system.
     *
     * @return array<int, \App\Entity\Role>
     */
    public function listRoles(): array
    {
        return $this->roleRepository->findAll();
    }

    /**
     * Checks if a user has the given permission code.
     */
    public function checkPermission(AppUser $user, string $actionCode): bool
    {
        foreach ($user->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $perm) {
                if ($perm->getCode() === $actionCode) {
                    return true;
                }
            }
        }
        return false;
    }

    // Optionally: get all permissions for a given user
    /**
     * @return array<string>
     */
    public function getUserPermissionCodes(AppUser $user): array
    {
        $codes = [];
        foreach ($user->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $perm) {
                $codes[] = $perm->getCode();
            }
        }
        return array_unique($codes);
    }
}
