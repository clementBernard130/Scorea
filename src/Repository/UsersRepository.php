<?php

namespace App\Repository;

use App\Entity\Sections;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * @return Users[]
     */
    public function findStudentsBySection(Sections $section): array
    {
        $users = $this->createQueryBuilder('user')
            ->innerJoin('user.sections', 'section')
            ->andWhere('section = :section')
            ->setParameter('section', $section)
            ->orderBy('user.last_name', 'ASC')
            ->addOrderBy('user.first_name', 'ASC')
            ->getQuery()
            ->getResult();

        $studentsById = [];
        foreach ($users as $user) {
            if (!$user instanceof Users || !in_array('ROLE_STUDENT', $user->getRoles(), true)) {
                continue;
            }

            $id = $user->getId();
            if ($id !== null) {
                $studentsById[$id] = $user;
            }
        }

        return array_values($studentsById);
    }

    /**
     * @return int[]
     */
    public function findStudentIdsForTeacher(Users $teacher): array
    {
        $users = $this->createQueryBuilder('user')
            ->innerJoin('user.sections', 'studentSection')
            ->innerJoin('studentSection.users', 'sectionUser')
            ->andWhere('sectionUser = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('user.last_name', 'ASC')
            ->addOrderBy('user.first_name', 'ASC')
            ->getQuery()
            ->getResult();

        $studentIds = [];
        foreach ($users as $user) {
            if (!$user instanceof Users || !in_array('ROLE_STUDENT', $user->getRoles(), true)) {
                continue;
            }

            $id = $user->getId();
            if ($id !== null) {
                $studentIds[$id] = (int) $id;
            }
        }

        return array_values($studentIds);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Users[] Returns an array of Users objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
