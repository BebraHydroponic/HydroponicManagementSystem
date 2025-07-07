<?php
namespace App\Service;

use App\Entity\HarvestEvent;
use App\Entity\InventoryLot;
use App\Entity\InventoryTransaction;
use App\Repository\InventoryLotRepository;
use App\Repository\InventoryTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InventoryService
{
    private EntityManagerInterface $em;
    private InventoryLotRepository $lotRepo;
    private InventoryTransactionRepository $txnRepo;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        InventoryLotRepository $lotRepo,
        InventoryTransactionRepository $txnRepo,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->lotRepo = $lotRepo;
        $this->txnRepo = $txnRepo;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function createInventoryLot(HarvestEvent $event, array $params): InventoryLot
{
    $lot = new InventoryLot();
    $lot->setHarvestEvent($event)
        ->setProductName($params['productName'] ?? '')
        ->setQuantity($params['quantity'] ?? 0)
        ->setCreatedAt(new \DateTimeImmutable());

    $errors = $this->validator->validate($lot);
    if (count($errors) > 0) {
        $this->logger->error('InventoryLot validation failed', ['errors' => (string)$errors]);
        throw new \InvalidArgumentException('InventoryLot validation failed: ' . (string)$errors);
    }

    $this->em->beginTransaction();
    try {
        $this->em->persist($lot);
        $this->em->flush();
        $this->em->commit();
    } catch (\Throwable $e) {
        $this->em->rollback();
        throw $e;
    }

    $this->dispatcher->dispatch(new class($lot) {
        private InventoryLot $lot;
        public function __construct(InventoryLot $lot) { $this->lot = $lot; }
        public function getInventoryLot(): InventoryLot { return $this->lot; }
    }, 'inventory.lot_created');

    return $lot;
}

public function logTransaction(InventoryLot $lot, string $type, float $quantity, array $notes = []): InventoryTransaction
{
    $txn = new InventoryTransaction();
    $txn->setInventoryLot($lot)
        ->setType($type)
        ->setQuantity($quantity)
        ->setNotes($notes)
        ->setCreatedAt(new \DateTimeImmutable());

    $errors = $this->validator->validate($txn);
    if (count($errors) > 0) {
        $this->logger->error('InventoryTransaction validation failed', ['errors' => (string)$errors]);
        throw new \InvalidArgumentException('InventoryTransaction validation failed: ' . (string)$errors);
    }

    $this->em->beginTransaction();
    try {
        $this->em->persist($txn);

        $qty = $lot->getQuantity();
        if ($type === 'IN') {
            $lot->setQuantity($qty + $quantity);
        } elseif ($type === 'OUT') {
            if ($quantity > $qty) {
                throw new \InvalidArgumentException('Cannot remove more than current stock.');
            }
            $lot->setQuantity($qty - $quantity);
        } else {
            throw new \InvalidArgumentException('Invalid transaction type.');
        }

        $this->em->flush();
        $this->em->commit();
    } catch (\Throwable $e) {
        $this->em->rollback();
        throw $e;
    }

    $this->dispatcher->dispatch(new class($txn) {
        private InventoryTransaction $txn;
        public function __construct(InventoryTransaction $txn) { $this->txn = $txn; }
        public function getTransaction(): InventoryTransaction { return $this->txn; }
    }, 'inventory.transaction_logged');

    return $txn;
}

}
