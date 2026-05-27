<?php

namespace App\Repository;

use App\Entity\Alerts;
use App\Entity\Subjects;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alerts>
 */
class AlertsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alerts::class);
    }

    /**
     * @param Subjects[] $subjects
     * @return Alerts[]
     */
    public function findBySubjects(array $subjects): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.users', 'u')
            ->addSelect('u')
            ->leftJoin('u.sections', 's')
            ->addSelect('s')
            ->leftJoin('a.subject', 'sub')
            ->addSelect('sub')
            ->andWhere('a.subject IN (:subjects)')
            ->setParameter('subjects', $subjects)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function existsByTypeSubjectAndStudent(string $type, Subjects $subject, Users $student): bool
    {
        $alerts = $this->findBy(['subject' => $subject, 'users' => $student]);

        foreach ($alerts as $alert) {
            if (in_array($type, $alert->getType() ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    public function removeByTypeSubjectAndStudent(string $type, Subjects $subject, Users $student): void
    {
        $alerts = $this->findBy(['subject' => $subject, 'users' => $student]);

        foreach ($alerts as $alert) {
            if (in_array($type, $alert->getType() ?? [], true)) {
                $this->getEntityManager()->remove($alert);
            }
        }

        $this->getEntityManager()->flush();
    }
}
