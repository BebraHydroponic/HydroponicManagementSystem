<?php
namespace App\Controller;

use App\Service\ProductionWorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/workflow')]
class ProductionWorkflowController extends AbstractController
{
    public function __construct(private ProductionWorkflowService $service) {}

    #[Route('/batch', name: 'workflow_sow_batch', methods: ['POST'])]
    public function sowBatch(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $batch = $this->service->sowBatch($data);

        return $this->json([
            'id' => $batch->getId(),
            'name' => $batch->getName(),
            'sowDate' => $batch->getSowDate()->format('c'),
        ], 201);
    }

    #[Route('/tray-row/{id}/move', name: 'workflow_move_tray_row', methods: ['POST'])]
    public function moveTrayRow(int $id, Request $request): JsonResponse
    {
        $placeId = json_decode($request->getContent(), true)['placeId'] ?? null;
        try {
            $tr = $this->service->moveTrayRow($id, $placeId);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(['id'=>$tr->getId(), 'place'=>$tr->getPlace()->getId()]);
    }

    #[Route('/tray-row/{id}/split', name: 'workflow_split_tray_row', methods: ['POST'])]
    public function splitTrayRow(int $id, Request $request): JsonResponse
    {
        $splits = json_decode($request->getContent(), true)['splits'] ?? [];
        try {
            $newRows = $this->service->splitTrayRow($id, $splits);
        } catch (\Throwable $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(array_map(fn($r)=>['id'=>$r->getId(),'qty'=>$r->getQuantity()], $newRows));
    }

    #[Route('/tray-row/{id}/retray', name: 'workflow_retray_tray_row', methods: ['POST'])]
    public function retrayTrayRow(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $tr = $this->service->retrayTrayRow($id, $data['newTrayId'], $data['rowIndex'], $data['placeId']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(['id'=>$tr->getId()]);
    }

    #[Route('/tray-row/{id}/harvest', name: 'workflow_log_harvest', methods: ['POST'])]
    public function logHarvest(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $h = $this->service->logHarvest($id, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(['id'=>$h->getId(), 'qty'=>$h->getQuantity()]);
    }

    #[Route('/batch/{id}/close', name: 'workflow_close_batch', methods: ['POST'])]
    public function closeBatch(int $id): JsonResponse
    {
        try {
            $b = $this->service->closeBatch($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(['id'=>$b->getId()]);
    }

    #[Route('/batch/{id}', name: 'workflow_destroy_batch', methods: ['DELETE'])]
    public function destroyBatch(int $id): JsonResponse
    {
        try {
            $this->service->destroyBatch($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return new JsonResponse(null, 204);
    }
}
