<?php
namespace App\Service;

use App\Entity\MaintenanceEvent;
use App\Repository\MaintenanceEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MaintenanceService
{
    private EntityManagerInterface $em;
    private MaintenanceEventRepository $repo;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        MaintenanceEventRepository $repo,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->repo = $repo;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function schedule(array $data): MaintenanceEvent
    {
        $event = new MaintenanceEvent();
        $event->setDescription($data['description'] ?? '')
              ->setScheduledAt(new \DateTimeImmutable($data['scheduledAt'] ?? 'now'))
              ->setCompleted(false);

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $this->logger->error('MaintenanceEvent validation failed', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('MaintenanceEvent validation failed: ' . (string)$errors);
        }

        $this->em->persist($event);
        $this->em->flush();

        $this->dispatcher->dispatch(new class($event) {
            private MaintenanceEvent $event;
            public function __construct(MaintenanceEvent $event) { $this->event = $event; }
            public function getMaintenanceEvent(): MaintenanceEvent { return $this->event; }
        }, 'maintenance.scheduled');

        return $event;
    }

    public function complete(int $eventId, array $data): MaintenanceEvent
    {
        $event = $this->repo->find($eventId);
        if (!$event) {
            throw new \InvalidArgumentException('Maintenance event not found.');
        }

        $event->setCompleted(true)
              ->setCompletedAt(new \DateTimeImmutable())
              ->setDescription($data['description'] ?? $event->getDescription());

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $this->logger->error('MaintenanceEvent completion validation failed', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('MaintenanceEvent completion validation failed: ' . (string)$errors);
        }

        $this->em->flush();

        $this->dispatcher->dispatch(new class($event) {
            private MaintenanceEvent $event;
            public function __construct(MaintenanceEvent $event) { $this->event = $event; }
            public function getMaintenanceEvent(): MaintenanceEvent { return $this->event; }
        }, 'maintenance.completed');

        return $event;
    }

    public function skip(int $eventId, array $data): MaintenanceEvent
    {
        $event = $this->repo->find($eventId);
        if (!$event) {
            throw new \InvalidArgumentException('Maintenance event not found.');
        }

        $event->setCompleted(false)
              ->setDescription($data['description'] ?? $event->getDescription());

        $this->em->flush();

        $this->dispatcher->dispatch(new class($event) {
            private MaintenanceEvent $event;
            public function __construct(MaintenanceEvent $event) { $this->event = $event; }
            public function getMaintenanceEvent(): MaintenanceEvent { return $this->event; }
        }, 'maintenance.skipped');

        return $event;
    }
}
