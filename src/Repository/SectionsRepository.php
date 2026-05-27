<?php

namespace App\Repository;

use App\Entity\Sections;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sections>
 */
class SectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sections::class);
    }

    /**
     * @return Sections[]
     */
    public function findForTeacher(Users $teacher): array
    {
        return $this->createQueryBuilder('section')
            ->distinct()
            ->innerJoin('section.users', 'teacher')
            ->andWhere('teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('section.start_date', 'DESC')
            ->addOrderBy('section.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Sections[] Returns an array of Sections objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sections
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
