<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class TeacherProfileController extends AbstractController
{
    #[Route('/teacher/profile', name: 'teacher_profile', methods: ['GET'])]
    public function index(): Response
    {
        $teacher = $this->getUser();
        if (!$teacher instanceof Users || !in_array('ROLE_TEACHER', $teacher->getRoles(), true)) {
            throw new AccessDeniedException('Acces reserve aux enseignants.');
        }

        $sections = [];
        $sectionNames = [];
        $trainingNames = [];
        $academicYears = [];
        $subjectNames = [];

        foreach ($teacher->getSections() as $section) {
            $training = $section->getTraining();
            $startDate = $section->getStartDate();
            $endDate = $section->getEndDate();

            $sectionName = $section->getName();
            if ($sectionName !== null) {
                $sectionNames[$sectionName] = $sectionName;
            }

            $trainingName = $training?->getName();
            if ($trainingName !== null) {
                $trainingNames[$trainingName] = $trainingName;
            }

            $academicYear = null;
            if ($startDate !== null && $endDate !== null) {
                $academicYear = $startDate->format('Y') . ' - ' . $endDate->format('Y');
            } elseif ($startDate !== null) {
                $academicYear = $startDate->format('Y');
            } elseif ($endDate !== null) {
                $academicYear = $endDate->format('Y');
            }

            if ($academicYear !== null) {
                $academicYears[$academicYear] = $academicYear;
            }

            $sections[] = [
                'name' => $sectionName,
                'training' => $trainingName,
                'academicYear' => $academicYear,
            ];
        }

        foreach ($teacher->getSubjects() as $subject) {
            $subjectName = $subject->getName();
            if ($subjectName !== null) {
                $subjectNames[$subjectName] = $subjectName;
            }
        }

        $createdAt = $teacher->getCreatedAt();
        $updatedAt = $teacher->getUpdatedAt();
        $deletedAt = $teacher->getDeletedAt();

        $nonUserRoles = array_values(array_filter(
            $teacher->getRoles(),
            static fn (string $role): bool => $role !== 'ROLE_USER'
        ));

        return $this->render('teacher/profile/profile.html.twig', [
            'teacher' => $teacher,
            'sections' => $sections,
            'sectionNames' => array_values($sectionNames),
            'trainingNames' => array_values($trainingNames),
            'academicYears' => array_values($academicYears),
            'subjectNames' => array_values($subjectNames),
            'primarySection' => $sectionNames !== [] ? array_values($sectionNames)[0] : null,
            'primaryTraining' => $trainingNames !== [] ? array_values($trainingNames)[0] : null,
            'primaryAcademicYear' => $academicYears !== [] ? array_values($academicYears)[0] : null,
            'roleLabels' => $this->getRoleLabels($teacher),
            'meta' => [
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'deletedAt' => $deletedAt,
                'rolesCount' => count($nonUserRoles),
                'sectionCount' => count($sections),
                'subjectCount' => count($subjectNames),
                'testCount' => $teacher->getTests()->count(),
            ],
        ]);
    }

    /**
     * @return array<int, array{role: string, label: string}>
     */
    private function getRoleLabels(Users $teacher): array
    {
        $labels = [];

        foreach ($teacher->getRoles() as $role) {
            if ($role === 'ROLE_USER') {
                continue;
            }

            $labels[] = [
                'role' => $role,
                'label' => match ($role) {
                    'ROLE_ADMIN' => 'Administrateur',
                    'ROLE_TEACHER' => 'Professeur',
                    'ROLE_ASSISTANT' => 'Assistant',
                    'ROLE_STUDENT' => 'Étudiant',
                    default => $role,
                },
            ];
        }

        return $labels;
    }
}