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

    /**
     * Charge tous les GradeTypeNames avec leurs GradeTypes et les Skills associés en une seule requête,
     * évitant les N+1 lors de l'accès à getGradeTypes() ou getSkill() dans les callbacks de formulaire.
     *
     * @return GradeTypeNames[]
     */
    public function findAllWithGradeTypesAndSkills(): array
    {
        return $this->createQueryBuilder('gtn')
            ->leftJoin('gtn.gradeTypes', 'gt')
            ->addSelect('gt')
            ->leftJoin('gt.skill', 's')
            ->addSelect('s')
            ->getQuery()
            ->getResult();
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
