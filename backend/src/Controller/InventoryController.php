<?php
namespace App\Controller;

use App\Service\InventoryService;
use App\Repository\HarvestEventRepository;
use App\Repository\InventoryLotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/inventory')]
class InventoryController extends AbstractController
{
    public function __construct(
        private InventoryService $inventoryService,
        private HarvestEventRepository $harvestRepo,
        private InventoryLotRepository $lotRepo
    ) {}

    #[Route('/lots', name: 'inventory_create_lot', methods: ['POST'])]
    public function createInventoryLot(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = $this->harvestRepo->find($data['harvestEventId'] ?? 0);
        if (!$event) {
            return $this->json(['error'=>'HarvestEvent not found'], 404);
        }

        $lot = $this->inventoryService->createInventoryLot($event, [
            'productName' => $data['productName'] ?? null,
            'quantity'    => $data['quantity'] ?? null,
        ]);

        return $this->json([
            'id' => $lot->getId(),
            'productName' => $lot->getProductName(),
            'quantity' => $lot->getQuantity(),
            'createdAt' => $lot->getCreatedAt()->format('c'),
        ], 201);
    }

    #[Route('/transactions', name: 'inventory_log_transaction', methods: ['POST'])]
    public function logTransaction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $lot = $this->lotRepo->find($data['inventoryLotId'] ?? 0);
        if (!$lot) {
            return $this->json(['error'=>'InventoryLot not found'], 404);
        }

        $txn = $this->inventoryService->logTransaction(
            $lot,
            $data['type'] ?? '',
            $data['quantity'] ?? 0,
            $data['notes'] ?? []
        );

        return $this->json([
            'id' => $txn->getId(),
            'type' => $txn->getType(),
            'quantity' => $txn->getQuantity(),
            'notes' => $txn->getNotes(),
            'createdAt' => $txn->getCreatedAt()->format('c'),
        ], 201);
    }
}
