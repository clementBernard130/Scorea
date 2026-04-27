<?php

namespace App\Repository;

use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Users;
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

    /**
     * @return int[]
     */
    public function findStudentIdsForTest(Tests $test): array
    {
        $results = $this->createQueryBuilder('grade')
            ->select('DISTINCT student.id AS student_id')
            ->innerJoin('grade.student', 'student')
            ->andWhere('grade.test = :test')
            ->setParameter('test', $test)
            ->getQuery()
            ->getArrayResult();

        return array_values(array_map(static fn (array $row): int => (int) $row['student_id'], $results));
    }

    public function studentAlreadyGradedForTest(Tests $test, Users $student, ?int $excludeGradeId = null): bool
    {
        $qb = $this->createQueryBuilder('grade')
            ->select('COUNT(grade.id)')
            ->andWhere('grade.test = :test')
            ->andWhere('grade.student = :student')
            ->setParameter('test', $test)
            ->setParameter('student', $student);

        if ($excludeGradeId !== null) {
            $qb
                ->andWhere('grade.id != :excludeGradeId')
                ->setParameter('excludeGradeId', $excludeGradeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
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
