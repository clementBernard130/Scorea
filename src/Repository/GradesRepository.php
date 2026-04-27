<?php

namespace App\Repository;

use App\Entity\Grades;
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

    /**
     * Retourne les IDs des élèves ayant au moins une note dans la matière.
     * Utilise une requête DQL directe pour éviter les problèmes de cache Doctrine.
     *
     * @return int[]
     */
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
