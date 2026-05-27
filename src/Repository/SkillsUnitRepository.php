<?php

namespace App\Repository;

use App\Entity\SkillsUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SkillsUnit>
 */
class SkillsUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillsUnit::class);
    }

    /**
     * @param list<int> $trainingIds
     *
     * @return list<SkillsUnit>
     */
    public function findByTrainingIds(array $trainingIds): array
    {
        if ($trainingIds === []) {
            return [];
        }

        return $this->createQueryBuilder('skillUnit')
            ->leftJoin('skillUnit.skills', 'skill')
            ->addSelect('skill')
            ->andWhere('IDENTITY(skillUnit.trainings) IN (:trainingIds)')
            ->setParameter('trainingIds', $trainingIds)
            ->orderBy('skillUnit.name', 'ASC')
            ->addOrderBy('skill.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
