<?php
namespace App\Controller;

use App\Service\MaintenanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/maintenance')]
class MaintenanceController extends AbstractController
{
    public function __construct(private MaintenanceService $maintenanceService) {}

    #[Route('', name: 'maintenance_schedule', methods: ['POST'])]
    public function scheduleMaintenance(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = $this->maintenanceService->schedule($data);

        return $this->json([
            'id' => $event->getId(),
            'description' => $event->getDescription(),
            'scheduledAt' => $event->getScheduledAt()->format('c'),
            'completed' => $event->isCompleted(),
        ], 201);
    }

    #[Route('/{id}/complete', name: 'maintenance_complete', methods: ['POST'])]
    public function completeMaintenance(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $event = $this->maintenanceService->complete($id, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }

        return $this->json([
            'id' => $event->getId(),
            'completedAt' => $event->getCompletedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}/skip', name: 'maintenance_skip', methods: ['POST'])]
    public function skipMaintenance(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $event = $this->maintenanceService->skip($id, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }

        return $this->json([
            'id' => $event->getId(),
            'completed' => $event->isCompleted(),
        ]);
    }
}
