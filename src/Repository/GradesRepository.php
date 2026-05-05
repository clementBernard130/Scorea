<?php

namespace App\Repository;

use App\Entity\Grades;
use App\Entity\Subjects;
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

    
    public function findForTestAndAllowedStudents(Tests $test, array $allowedStudentIds): array
    {
        if ($allowedStudentIds === []) {
            return [];
        }

        return $this->createQueryBuilder('grade')
            ->innerJoin('grade.student', 'student')
            ->addSelect('student')
            ->innerJoin('grade.test', 'test')
            ->addSelect('test')
            ->innerJoin('test.gradeType', 'gradeType')
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

    /**
     * @return Grades[]
     */
    public function findHistoryForStudent(
        Users $student,
        ?int $subjectId = null,
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null,
        int $limit = 150
    ): array {
        $qb = $this->createQueryBuilder('grade')
            ->innerJoin('grade.test', 'test')
            ->addSelect('test')
            ->innerJoin('test.subject', 'subject')
            ->addSelect('subject')
            ->innerJoin('test.teacher', 'teacher')
            ->addSelect('teacher')
            ->innerJoin('test.gradeType', 'gradeType')
            ->addSelect('gradeType')
            ->andWhere('grade.student = :student')
            ->setParameter('student', $student)
            ->orderBy('test.testDate', 'DESC')
            ->addOrderBy('grade.id', 'DESC')
            ->setMaxResults($limit);

        if ($subjectId !== null) {
            $qb
                ->andWhere('subject.id = :subjectId')
                ->setParameter('subjectId', $subjectId);
        }

        if ($from !== null) {
            $qb
                ->andWhere('test.testDate >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb
                ->andWhere('test.testDate <= :to')
                ->setParameter('to', $to);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function findEvaluatedSubjectsForStudent(Users $student): array
    {
        $rows = $this->createQueryBuilder('grade')
            ->select('DISTINCT subject.id AS id, subject.name AS name')
            ->innerJoin('grade.test', 'test')
            ->innerJoin('test.subject', 'subject')
            ->andWhere('grade.student = :student')
            ->setParameter('student', $student)
            ->orderBy('subject.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_values(array_filter(array_map(
            static function (array $row): ?array {
                $id = isset($row['id']) ? (int) $row['id'] : null;
                $name = isset($row['name']) ? (string) $row['name'] : null;
                if ($id === null || $name === null || $name === '') {
                    return null;
                }

                return [
                    'id' => $id,
                    'name' => $name,
                ];
            },
            $rows
        )));
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
