<?php

namespace App\Controller;

use App\Service\RBACService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/rbac')]
class RBACController extends AbstractController
{
    private RBACService $rbacService;

    public function __construct(RBACService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    #[Route('/permissions', methods: ['GET'])]
    public function listPermissions(): JsonResponse
    {
        $permissions = $this->rbacService->listPermissions();

        $data = array_map(function ($perm) {
            return [
                'id' => $perm->getId(),
                'code' => $perm->getCode(),
                'description' => $perm->getDescription(),
            ];
        }, $permissions);

        return $this->json($data);
    }

    #[Route('/roles', methods: ['GET'])]
    public function listRoles(): JsonResponse
    {
        $roles = $this->rbacService->listRoles();

        $data = array_map(function ($role) {
            return [
                'id' => $role->getId(),
                'code' => $role->getCode(),
                'description' => $role->getDescription(),
            ];
        }, $roles);

        return $this->json($data);
    }
}
