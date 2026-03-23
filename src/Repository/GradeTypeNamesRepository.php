<?php

namespace App\Repository;

use App\Entity\GradeTypeNames;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GradeTypeNames>
 */
class GradeTypeNamesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GradeTypeNames::class);
    }

    //    /**
    //     * @return GradeTypeNames[] Returns an array of GradeTypeNames objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GradeTypeNames
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
