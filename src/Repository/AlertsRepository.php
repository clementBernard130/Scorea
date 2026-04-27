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
