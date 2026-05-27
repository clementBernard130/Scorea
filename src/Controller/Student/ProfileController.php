<?php

namespace App\Controller\Student;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ProfileController extends AbstractController
{
    #[Route('/student/profile', name: 'student_profile', methods: ['GET'])]
    public function index(): Response
    {
        $student = $this->getUser();
        if (!$student instanceof Users || !in_array('ROLE_STUDENT', $student->getRoles(), true)) {
            throw new AccessDeniedException('Acces reserve aux apprentis.');
        }

        $sections = [];
        $sectionNames = [];
        $trainingNames = [];
        $academicYears = [];

        foreach ($student->getSections() as $section) {
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

        $createdAt = $student->getCreatedAt();
        $updatedAt = $student->getUpdatedAt();
        $deletedAt = $student->getDeletedAt();

        return $this->render('student/profile/profile.html.twig', [
            'student' => $student,
            'sections' => $sections,
            'sectionNames' => array_values($sectionNames),
            'trainingNames' => array_values($trainingNames),
            'academicYears' => array_values($academicYears),
            'primarySection' => $sectionNames !== [] ? array_values($sectionNames)[0] : null,
            'primaryTraining' => $trainingNames !== [] ? array_values($trainingNames)[0] : null,
            'primaryAcademicYear' => $academicYears !== [] ? array_values($academicYears)[0] : null,
            'roleLabels' => $this->getRoleLabels($student),
            'meta' => [
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'deletedAt' => $deletedAt,
                'rolesCount' => max(0, count(array_filter($student->getRoles(), static fn (string $role): bool => $role !== 'ROLE_USER'))),
                'sectionCount' => count($sections),
            ],
        ]);
    }

    /**
     * @return array<int, array{role: string, label: string}>
     */
    private function getRoleLabels(Users $student): array
    {
        $labels = [];

        foreach ($student->getRoles() as $role) {
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