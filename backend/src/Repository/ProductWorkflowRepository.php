<?php

namespace App\Repository;

use App\Entity\ProductWorkflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductWorkflow>
 *
 * @method ProductWorkflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductWorkflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductWorkflow[]    findAll()
 * @method ProductWorkflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductWorkflowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductWorkflow::class);
    }

    // Add custom queries here as needed, e.g.
    // public function findByStage(string $stage): array
    // {
    //     return $this->createQueryBuilder('pw')
    //         ->andWhere('pw.stage = :stage')
    //         ->setParameter('stage', $stage)
    //         ->orderBy('pw.id', 'ASC')
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
}
