<?php
namespace App\Service;

use App\Entity\Batch;
use App\Entity\TrayRow;
use App\Entity\HarvestEvent;
use App\Repository\BatchRepository;
use App\Repository\TrayRowRepository;
use App\Repository\HarvestEventRepository;
use App\Repository\TrayRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductionWorkflowService
{
    private EntityManagerInterface $em;
    private BatchRepository $batchRepo;
    private TrayRowRepository $trayRowRepo;
    private HarvestEventRepository $harvestRepo;
    private TrayRepository $trayRepo;
    private PlaceRepository $placeRepo;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        BatchRepository $batchRepo,
        TrayRowRepository $trayRowRepo,
        HarvestEventRepository $harvestRepo,
        TrayRepository $trayRepo,
        PlaceRepository $placeRepo,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->batchRepo = $batchRepo;
        $this->trayRowRepo = $trayRowRepo;
        $this->harvestRepo = $harvestRepo;
        $this->trayRepo = $trayRepo;
        $this->placeRepo = $placeRepo;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function sowBatch(array $data): Batch
    {
        $batch = new Batch();
        $batch->setName($data['name'] ?? '')
              ->setSowDate(new \DateTimeImmutable())
              ->setClosed(false);

        $errors = $this->validator->validate($batch);
        if (count($errors) > 0) {
            $this->logger->error('Batch validation failed', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('Batch validation failed: ' . (string)$errors);
        }

        $this->em->persist($batch);
        $this->em->flush();

        $this->dispatcher->dispatch(new class($batch) {
            private Batch $batch;
            public function __construct(Batch $batch) { $this->batch = $batch; }
            public function getBatch(): Batch { return $this->batch; }
        }, 'batch.sown');

        return $batch;
    }

    public function moveTrayRow(int $trayRowId, int $placeId): TrayRow
    {
        $trayRow = $this->trayRowRepo->find($trayRowId);
        $place = $this->placeRepo->find($placeId);

        if (!$trayRow || !$place) {
            throw new \InvalidArgumentException('TrayRow or Place not found.');
        }

        $trayRow->setPlace($place);

        $errors = $this->validator->validate($trayRow);
        if (count($errors) > 0) {
            $this->logger->error('TrayRow validation failed on move', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('TrayRow validation failed on move: ' . (string)$errors);
        }

        $this->em->flush();

        $this->dispatcher->dispatch(new class($trayRow) {
            private TrayRow $trayRow;
            public function __construct(TrayRow $trayRow) { $this->trayRow = $trayRow; }
            public function getTrayRow(): TrayRow { return $this->trayRow; }
        }, 'trayrow.moved');

        return $trayRow;
    }

    public function splitTrayRow(int $trayRowId, array $splits): array
    {
        $trayRow = $this->trayRowRepo->find($trayRowId);
        if (!$trayRow) {
            throw new \InvalidArgumentException('TrayRow not found.');
        }

        $results = [];

        $this->em->beginTransaction();
        try {
            $originalQty = $trayRow->getQuantity();
            $totalSplitQty = 0;

            foreach ($splits as $splitData) {
                $qty = $splitData['qty'] ?? 0;
                if ($qty <= 0) {
                    throw new \InvalidArgumentException('Split quantity must be positive.');
                }
                $totalSplitQty += $qty;

                $newRow = clone $trayRow;
                $newRow->setQuantity($qty);
                $this->em->persist($newRow);
                $results[] = $newRow;
            }

            if ($totalSplitQty > $originalQty) {
                throw new \InvalidArgumentException('Sum of split quantities exceeds original quantity.');
            }

            $trayRow->setQuantity($originalQty - $totalSplitQty);

            $errors = $this->validator->validate($trayRow);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException('TrayRow validation failed after split: ' . (string)$errors);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            $this->logger->error('Error during tray row split', ['exception' => $e]);
            throw $e;
        }

        return $results;
    }

    public function retrayTrayRow(int $trayRowId, int $newTrayId, int $rowIndex, int $placeId): TrayRow
    {
        $trayRow = $this->trayRowRepo->find($trayRowId);
        $tray = $this->trayRepo->find($newTrayId);
        $place = $this->placeRepo->find($placeId);

        if (!$trayRow || !$tray || !$place) {
            throw new \InvalidArgumentException('Invalid retray request parameters.');
        }

        $trayRow->setTray($tray)
                ->setRowIndex($rowIndex)
                ->setPlace($place);

        $errors = $this->validator->validate($trayRow);
        if (count($errors) > 0) {
            $this->logger->error('TrayRow validation failed on retray', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('TrayRow validation failed on retray: ' . (string)$errors);
        }

        $this->em->flush();

        $this->dispatcher->dispatch(new class($trayRow) {
            private TrayRow $trayRow;
            public function __construct(TrayRow $trayRow) { $this->trayRow = $trayRow; }
            public function getTrayRow(): TrayRow { return $this->trayRow; }
        }, 'trayrow.retrayed');

        return $trayRow;
    }

    public function logHarvest(int $trayRowId, array $harvestData): HarvestEvent
    {
        $trayRow = $this->trayRowRepo->find($trayRowId);
        if (!$trayRow) {
            throw new \InvalidArgumentException('TrayRow not found.');
        }

        $event = new HarvestEvent();
        $event->setTrayRow($trayRow)
              ->setQuantity($harvestData['quantity'] ?? 0)
              ->setHarvestedAt(new \DateTimeImmutable($harvestData['harvestedAt'] ?? 'now'));

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $this->logger->error('HarvestEvent validation failed', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('HarvestEvent validation failed: ' . (string)$errors);
        }

        $this->em->persist($event);
        $this->em->flush();

        $this->dispatcher->dispatch(new class($event) {
            private HarvestEvent $event;
            public function __construct(HarvestEvent $event) { $this->event = $event; }
            public function getHarvestEvent(): HarvestEvent { return $this->event; }
        }, 'harvest.logged');

        return $event;
    }

    public function closeBatch(int $batchId): Batch
    {
        $batch = $this->batchRepo->find($batchId);
        if (!$batch) {
            throw new \InvalidArgumentException('Batch not found.');
        }

        $batch->setClosed(true);

        $errors = $this->validator->validate($batch);
        if (count($errors) > 0) {
            $this->logger->error('Batch validation failed on close', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('Batch validation failed on close: ' . (string)$errors);
        }

        $this->em->flush();

        $this->dispatcher->dispatch(new class($batch) {
            private Batch $batch;
            public function __construct(Batch $batch) { $this->batch = $batch; }
            public function getBatch(): Batch { return $this->batch; }
        }, 'batch.closed');

        return $batch;
    }

    public function destroyBatch(int $batchId): void
    {
        $batch = $this->batchRepo->find($batchId);
        if (!$batch) {
            throw new \InvalidArgumentException('Batch not found.');
        }

        $this->em->remove($batch);
        $this->em->flush();

        $this->dispatcher->dispatch(new class($batch) {
            private Batch $batch;
            public function __construct(Batch $batch) { $this->batch = $batch; }
            public function getBatch(): Batch { return $this->batch; }
        }, 'batch.destroyed');
    }
}
