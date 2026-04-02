<?php

namespace App\Service;

use App\Entity\Alerts;
use App\Entity\Grades;
use App\Entity\Sections;
use App\Entity\Subjects;
use App\Entity\Users;
use App\Repository\AlertsRepository;
use Doctrine\ORM\EntityManagerInterface;

class AlertService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AlertsRepository $alertsRepository,
    ) {}

    /**
     * À appeler lors de la création ou modification d'une note.
     * Crée une alerte 'lower_average' si la note est inférieure à 10.
     */
    public function handleGradeCreated(Grades $grade): void
    {
        if ($grade->getGrade() < 10) {
            $this->createLowerAverageAlert($grade);
        }
    }

    /**
     * À appeler pour vérifier les notes manquantes dans une matière pour une classe.
     * Compare tous les élèves de la section avec ceux ayant au moins une note dans la matière.
     */
    public function handleMissingGrades(Subjects $subject, Sections $section): void
    {
        $students = $section->getUsers()->filter(
            fn(Users $u) => in_array('ROLE_STUDENT', $u->getRoles(), true)
        );

        $studentsWithGrades = [];
        foreach ($subject->getTests() as $test) {
            foreach ($test->getGrades() as $grade) {
                $studentsWithGrades[$grade->getStudent()->getId()] = true;
            }
        }

        foreach ($students as $student) {
            if (isset($studentsWithGrades[$student->getId()])) {
                continue;
            }

            if ($this->alertsRepository->existsByTypeSubjectAndStudent('missing_grade', $subject, $student)) {
                continue;
            }

            $this->createAlert(
                'missing_grade',
                'Note manquante',
                sprintf('Aucune note pour %s en %s', $student, $subject->getName()),
                $subject,
                $student
            );
        }

        $this->em->flush();
    }

    private function createLowerAverageAlert(Grades $grade): void
    {
        $subject = $grade->getTest()->getSubject();
        $student = $grade->getStudent();

        if ($this->alertsRepository->existsByTypeSubjectAndStudent('lower_average', $subject, $student)) {
            return;
        }

        $this->createAlert(
            'lower_average',
            'Note inférieure à la moyenne',
            sprintf('%s a obtenu %.2f/20 en %s', $student, $grade->getGrade(), $subject->getName()),
            $subject,
            $student
        );

        $this->em->flush();
    }

    private function createAlert(string $type, string $name, string $description, Subjects $subject, Users $student): void
    {
        $alert = new Alerts();
        $alert->setType([$type]);
        $alert->setDate(new \DateTime());
        $alert->setName($name);
        $alert->setDescription($description);
        $alert->setSubject($subject);
        $alert->setUsers($student);

        $this->em->persist($alert);
    }
}
