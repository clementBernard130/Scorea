<?php

namespace App\Controller;

use App\Entity\Sections;
use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\GradesRepository;
use App\Repository\TestsRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ClassController extends AbstractController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private TestsRepository $testsRepository,
        private GradesRepository $gradesRepository
    ) {
    }

    #[Route('/classes/{id}', name: 'app_class_show', requirements: ['id' => '\\d+'])]
    public function show(Sections $section): Response
    {
        $teacher = $this->getTeacherUser();

        if (!$teacher->getSections()->contains($section)) {
            throw new AccessDeniedException('Vous n\'avez pas accès à cette classe.');
        }

        $students = $this->usersRepository->findStudentsBySection($section);
        $tests = $this->testsRepository->findByTeacher($teacher);

        return $this->render('class/show.html.twig', [
            'user' => $teacher,
            'section' => $section,
            'students' => $students,
            'tests' => $tests,
        ]);
    }

    #[Route('/tests/{id}', name: 'app_test_show', requirements: ['id' => '\\d+'])]
    public function showTest(Request $request, Tests $test): Response
    {
        $teacher = $this->getTeacherUser();

        if ($test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Vous n\'avez pas accès à ce test.');
        }

        $section = null;
        $allowedStudentIds = $this->usersRepository->findStudentIdsForTeacher($teacher);

        $sectionId = $request->query->getInt('section');
        if ($sectionId > 0) {
            $section = $teacher->getSections()->findFirst(
                static fn (int $_, Sections $teacherSection): bool => $teacherSection->getId() === $sectionId
            );

            if (!$section instanceof Sections) {
                throw new AccessDeniedException('Vous n\'avez pas accès à cette classe.');
            }

            $allowedStudentIds = array_values(array_filter(array_map(
                static fn (Users $student): ?int => $student->getId(),
                $this->usersRepository->findStudentsBySection($section)
            )));
        }

        $grades = $this->gradesRepository->findForTestAndAllowedStudents($test, $allowedStudentIds);

        $gradeValues = array_values(array_filter(array_map(
            static fn (Grades $grade): ?float => $grade->getGrade(),
            $grades
        ), static fn (?float $value): bool => $value !== null));

        $gradeCount = count($gradeValues);
        $averageGrade = $gradeCount > 0 ? array_sum($gradeValues) / $gradeCount : null;
        $minGrade = $gradeCount > 0 ? min($gradeValues) : null;
        $maxGrade = $gradeCount > 0 ? max($gradeValues) : null;

        return $this->render('tests/show.html.twig', [
            'user' => $teacher,
            'test' => $test,
            'section' => $section,
            'grades' => $grades,
            'gradeCount' => $gradeCount,
            'averageGrade' => $averageGrade,
            'minGrade' => $minGrade,
            'maxGrade' => $maxGrade,
        ]);
    }

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();

        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }
}