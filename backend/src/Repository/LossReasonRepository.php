<?php

namespace App\Repository;

use App\Entity\LossReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LossReason>
 *
 * @method LossReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method LossReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method LossReason[]    findAll()
 * @method LossReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LossReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LossReason::class);
    }

    // Add custom queries here as needed
}
