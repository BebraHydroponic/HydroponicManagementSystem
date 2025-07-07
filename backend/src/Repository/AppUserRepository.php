<?php

namespace App\Repository;

use App\Entity\AppUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppUser>
 */
class AppUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppUser::class);
    }
    
    // public function findActiveUsers(): array
    // {
    //     return $this->createQueryBuilder('u')
    //         ->where('u.locked = false')
    //         ->getQuery()
    //         ->getResult();
    // }
}
