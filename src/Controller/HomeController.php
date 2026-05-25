<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\StudentSkillsPageBuilder;

use App\Entity\Sections;
use App\Repository\SectionsRepository;
use App\Repository\TestsRepository;
use App\Repository\GradesRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    public function __construct(
        private SectionsRepository $sectionsRepository,
        private TestsRepository $testsRepository,
        private GradesRepository $gradesRepository,
        private AlertsRepository $alertsRepository,
    ) {
    }

    #[Route('/', name: 'app_root')]
    public function index(): Response
    {
        // If user is authenticated, redirect to /home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // If not authenticated, redirect to login
        return $this->redirectToRoute('app_login');
    }

    #[Route('/home', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function home(StudentSkillsPageBuilder $studentSkillsPageBuilder): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin');
        }
        
        if ($this->isGranted('ROLE_STUDENT') && $user instanceof Users) {
            $primarySection = null;
            $sections = $user->getSections();
            if (!$sections->isEmpty()) {
                $primarySection = $sections->first()->getName();
            }
            
            return $this->render('student/skills/index.html.twig', [
                'page' => $studentSkillsPageBuilder->build($user),
                'user' => $user,
                'student' => $user,
                'primarySection' => $primarySection,
            ]);
        }
        
        $teacherSections = [];
        $teacherTestsCount = 0;
        $teacherAlerts = [];

        if ($user instanceof Users && in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            $sections = $this->sectionsRepository->findForTeacher($user);
            $teacherSections = array_map(
                function (Sections $section) use ($user): array {
                    $students = array_filter(
                        $section->getUsers()->toArray(),
                        static fn (Users $sectionUser): bool => in_array('ROLE_STUDENT', $sectionUser->getRoles(), true)
                    );

                    // Calculate ungraded students count
                    $sectionStudentIds = array_values(array_filter(array_map(
                        static fn (Users $student): ?int => $student->getId(),
                        $students
                    )));

                    $tests = array_filter(
                        $section->getTests()->toArray(),
                        static fn (Tests $test): bool => $test->getTeacher()?->getId() === $user->getId()
                    );
                    $totalUngradedCount = 0;

                    foreach ($tests as $test) {
                        $gradedStudentIds = $this->gradesRepository->findStudentIdsForTest($test);
                        $ungradedCount = count(array_diff($sectionStudentIds, $gradedStudentIds));
                        $totalUngradedCount += $ungradedCount;
                    }

                    return [
                        'section' => $section,
                        'studentCount' => count($students),
                        'ungradedStudentCount' => $totalUngradedCount,
                    ];
                },
                $sections
            );

            $teacherTests = $this->testsRepository->findByTeacher($user);
            $teacherTestsCount = count($teacherTests);

            // Build lookup [subjectId][sectionId] => most recent test (tests are already sorted DESC)
            $testBySubjectAndSection = [];
            foreach ($teacherTests as $test) {
                $subjectId = $test->getSubject()?->getId();
                $sectionId = $test->getSection()?->getId();
                if ($subjectId === null || $sectionId === null) {
                    continue;
                }
                if (!isset($testBySubjectAndSection[$subjectId][$sectionId])) {
                    $testBySubjectAndSection[$subjectId][$sectionId] = $test;
                }
            }

            $subjectsById = [];
            foreach ($teacherTests as $test) {
                $subject = $test->getSubject();
                if ($subject !== null) {
                    $subjectsById[$subject->getId()] = $subject;
                }
            }
            $teacherSubjects = array_values($subjectsById);
            $rawAlerts = $teacherSubjects !== [] ? $this->alertsRepository->findBySubjects($teacherSubjects) : [];

            $teacherAlerts = [];
            foreach ($rawAlerts as $alert) {
                $test = $alert->getTest();
                $sectionId = $alert->getSection()?->getId();

                if ($test === null) {
                    continue;
                }

                if (in_array('missing_grade', $alert->getType() ?? [], true) && $sectionId === null) {
                    continue;
                }

                $teacherAlerts[] = [
                    'alert' => $alert,
                    'testId' => $test->getId(),
                    'sectionId' => $sectionId,
                    'studentId' => $alert->getUsers()?->getId(),
                ];
            }
        }

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'teacherSections' => $teacherSections,
            'teacherTestsCount' => $teacherTestsCount,
            'teacherAlerts' => $teacherAlerts,
        ]);
    }
}
