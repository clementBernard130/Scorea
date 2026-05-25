<?php

namespace App\Repository;

use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Users;
use App\Entity\Subjects;
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
  
    public function findMostRecentByStudentAndSubject(Users $student, Subjects $subject): ?Grades
    {
        return $this->createQueryBuilder('g')
            ->join('g.test', 't')
            ->addSelect('t')
            ->andWhere('g.student = :student')
            ->andWhere('t.subject = :subject')
            ->setParameter('student', $student)
            ->setParameter('subject', $subject)
            ->orderBy('t.testDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findStudentIdsWithGradesInSubject(Subjects $subject): array
    {
        $results = $this->createQueryBuilder('g')
            ->select('IDENTITY(g.student) AS student_id')
            ->join('g.test', 't')
            ->where('t.subject = :subject')
            ->setParameter('subject', $subject)
            ->distinct()
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'student_id');
    }
}
