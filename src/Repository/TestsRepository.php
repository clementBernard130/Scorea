<?php

namespace App\Repository;

use App\Entity\Tests;
use App\Entity\Sections;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tests>
 */
class TestsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tests::class);
    }

    /**
     * @return Tests[]
     */
    public function findByTeacher(Users $teacher): array
    {
        return $this->createQueryBuilder('test')
            ->andWhere('test.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('test.testDate', 'DESC')
            ->addOrderBy('test.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tests[]
     */
    public function findByTeacherAndSection(Users $teacher, Sections $section): array
    {
        return $this->createQueryBuilder('test')
            ->andWhere('test.teacher = :teacher')
            ->andWhere('test.section = :section')
            ->setParameter('teacher', $teacher)
            ->setParameter('section', $section)
            ->orderBy('test.testDate', 'DESC')
            ->addOrderBy('test.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Tests[] Returns an array of Tests objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tests
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
