<?php

namespace App\Service;

use App\Entity\Alerts;
use App\Entity\Grades;
use App\Entity\Sections;
use App\Entity\Subjects;
use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\AlertsRepository;
use App\Repository\GradesRepository;
use Doctrine\ORM\EntityManagerInterface;

class AlertService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AlertsRepository $alertsRepository,
        private GradesRepository $gradesRepository,
    ) {}

    /**
     * À appeler lors de la création d'une note dans GradesCrudController::persistEntity().
     *
     * - Supprime l'alerte 'missing_grade' pour l'élève noté (il a maintenant une note).
     * - Crée une alerte 'lower_average' si la note est inférieure à 10.
     * - Crée des alertes 'missing_grade' pour les autres élèves de la même section
     *   uniquement si au moins 1 élève a désormais une note dans la matière.
     */
    public function handleGradeCreated(Grades $grade): void
    {
        $subject = $grade->getTest()->getSubject();
        $student = $grade->getStudent();

        $this->alertsRepository->removeByTypeSubjectAndStudent('missing_grade', $subject, $student);

        if ($grade->getGrade() < 10) {
            $this->createLowerAverageAlert($grade);
        }

        foreach ($student->getSections() as $section) {
            $this->createMissingGradeAlertsForSection($subject, $section, $grade->getTest());
        }

        $this->em->flush();
    }

    /**
     * À appeler lors de la modification d'une note.
     *
     * - Supprime l'alerte 'lower_average' existante pour l'élève/matière.
     * - Recrée une alerte 'lower_average' si la nouvelle note est inférieure à 10.
     */
    public function handleGradeUpdated(Grades $grade): void
    {
        $subject = $grade->getTest()->getSubject();
        $student = $grade->getStudent();

        $this->alertsRepository->removeByTypeSubjectAndStudent('lower_average', $subject, $student);

        if ($grade->getGrade() < 10) {
            $this->createLowerAverageAlert($grade);
            $this->em->flush();
        }
    }

    /**
     * Peut être appelé manuellement (action admin, commande) pour vérifier
     * les notes manquantes dans une matière pour une section donnée.
     */
    public function handleMissingGrades(Subjects $subject, Sections $section, Tests $triggeringTest): void
    {
        $this->createMissingGradeAlertsForSection($subject, $section, $triggeringTest);
        $this->em->flush();
    }

    private function createMissingGradeAlertsForSection(Subjects $subject, Sections $section, Tests $triggeringTest): void
    {
        // Requête DQL directe pour éviter le cache des collections Doctrine
        $studentIdsWithGrades = $this->gradesRepository->findStudentIdsWithGradesInSubject($subject);

        // Condition : aucune alerte si personne n'a encore de note dans la matière
        if (empty($studentIdsWithGrades)) {
            return;
        }

        $students = $section->getUsers()->filter(
            fn(Users $u) => in_array('ROLE_STUDENT', $u->getRoles(), true)
        );

        foreach ($students as $student) {
            if (in_array($student->getId(), $studentIdsWithGrades, true)) {
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
                $student,
                $triggeringTest,
                $section
            );
        }
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
            $student,
            $grade->getTest(),
            $grade->getTest()->getSection()
        );
    }

    private function createAlert(string $type, string $name, string $description, Subjects $subject, Users $student, Tests $test, ?Sections $section = null): void
    {
        $alert = new Alerts();
        $alert->setType([$type]);
        $alert->setDate(new \DateTime());
        $alert->setName($name);
        $alert->setDescription($description);
        $alert->setSubject($subject);
        $alert->setUsers($student);
        $alert->setTest($test);
        $alert->setSection($section);

        $this->em->persist($alert);
    }
}
