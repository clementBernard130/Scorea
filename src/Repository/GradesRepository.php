<?php

namespace App\Repository;

use App\Entity\Grades;
use App\Entity\Tests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grades>
 */
class GradesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grades::class);
    }

    /**
     * @param int[] $allowedStudentIds
     *
     * @return Grades[]
     */
    public function findForTestAndAllowedStudents(Tests $test, array $allowedStudentIds): array
    {
        if ($allowedStudentIds === []) {
            return [];
        }

        return $this->createQueryBuilder('grade')
            ->innerJoin('grade.student', 'student')
            ->addSelect('student')
            ->innerJoin('grade.gradeType', 'gradeType')
            ->addSelect('gradeType')
            ->andWhere('grade.test = :test')
            ->andWhere('student.id IN (:allowedStudentIds)')
            ->setParameter('test', $test)
            ->setParameter('allowedStudentIds', $allowedStudentIds)
            ->orderBy('student.last_name', 'ASC')
            ->addOrderBy('student.first_name', 'ASC')
            ->addOrderBy('grade.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Grades[] Returns an array of Grades objects
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

    //    public function findOneBySomeField($value): ?Grades
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
