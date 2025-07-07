<?php

namespace App\Controller;

use App\Service\AuditService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/audit')]
class AuditController extends AbstractController
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    #[Route('/logs', methods: ['GET'])]
    public function listLogs(Request $request): JsonResponse
    {
        $logs = $this->auditService->getAllLogs();

        $data = array_map(function ($log) {
            return [
                'id' => $log->getId(),
                'user' => $log->getUser(),
                'action' => $log->getAction(),
                'entityType' => $log->getEntityType(),
                'entityId' => $log->getEntityId(),
                'target' => $log->getTarget(),
                'details' => $log->getDetails(),
                'performedAt' => $log->getPerformedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $logs);

        return $this->json($data);
    }

    #[Route('/logs/{id}', methods: ['GET'])]
    public function getLog(int $id): JsonResponse
    {
        $log = $this->auditService->getLogById($id);

        if (!$log) {
            return $this->json(['error' => 'Log not found'], 404);
        }

        return $this->json([
            'id' => $log->getId(),
            'user' => $log->getUser(),
            'action' => $log->getAction(),
            'entityType' => $log->getEntityType(),
            'entityId' => $log->getEntityId(),
            'target' => $log->getTarget(),
            'details' => $log->getDetails(),
            'performedAt' => $log->getPerformedAt()?->format('Y-m-d H:i:s'),
        ]);
    }
}
