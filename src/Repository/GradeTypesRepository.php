<?php

namespace App\Repository;

use App\Entity\GradeTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GradeTypes>
 */
class GradeTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GradeTypes::class);
    }

    /**
     * Retourne en une seule requête scalaire les données de pondération pour un ensemble de matières.
     * Aucune association lazy n'est déclenchée : toutes les valeurs nécessaires à la construction des
     * labels de type d'éval sont incluses dans les lignes de résultat.
     *
     * @param int[] $subjectIds
     * @return list<array{subjectId: int, typeId: int, weight: int, skillName: string}>
     */
    public function findWeightRowsBySubjectIds(array $subjectIds): array
    {
        if ($subjectIds === []) {
            return [];
        }

        /** @var list<array{subjectId: int, typeId: int, weight: int, skillName: string}> */
        return $this->createQueryBuilder('gt')
            ->select('sub.id AS subjectId, gtn.id AS typeId, gt.weight AS weight, s.name AS skillName')
            ->join('gt.type', 'gtn')
            ->join('gt.skill', 's')
            ->join('s.subjects', 'sub')
            ->where('sub.id IN (:ids)')
            ->setParameter('ids', $subjectIds)
            ->getQuery()
            ->getScalarResult();
    }

    //    /**
    //     * @return GradeTypes[] Returns an array of GradeTypes objects
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

    //    public function findOneBySomeField($value): ?GradeTypes
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
