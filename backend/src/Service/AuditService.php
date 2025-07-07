<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuditService
{
    private AuditLogRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(AuditLogRepository $repo, EntityManagerInterface $em)
    {
        $this->repo = $repo;
        $this->em = $em;
    }

    public function getAllLogs(): array
    {
        return $this->repo->findAll();
    }

    public function getLogById(int $id): ?AuditLog
    {
        return $this->repo->find($id);
    }

    public function logAction(
        string $user,
        string $action,
        ?string $entityType,
        ?string $entityId,
        ?string $target,
        ?string $details
    ): AuditLog
    {
        $log = new AuditLog();
        $log->setUser($user)
            ->setAction($action)
            ->setEntityType($entityType)
            ->setEntityId($entityId)
            ->setTarget($target)
            ->setDetails($details)
            ->setPerformedAt(new \DateTimeImmutable());

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }
}
