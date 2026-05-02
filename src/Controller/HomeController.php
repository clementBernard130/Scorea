<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\StudentSkillsPageBuilder;

use App\Entity\Sections;
use App\Repository\SectionsRepository;
use App\Repository\TestsRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private SectionsRepository $sectionsRepository,
        private TestsRepository $testsRepository
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(StudentSkillsPageBuilder $studentSkillsPageBuilder): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_STUDENT') && $user instanceof Users) {
            return $this->render('student/skills/index.html.twig', [
                'page' => $studentSkillsPageBuilder->build($user),
                'user' => $user,
            ]);
        }
        
        $teacherSections = [];
        $teacherTestsCount = 0;

        if ($user instanceof Users && in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            $sections = $this->sectionsRepository->findForTeacher($user);
            $teacherSections = array_map(
                static function (Sections $section): array {
                    $students = array_filter(
                        $section->getUsers()->toArray(),
                        static fn (Users $sectionUser): bool => in_array('ROLE_STUDENT', $sectionUser->getRoles(), true)
                    );

                    return [
                        'section' => $section,
                        'studentCount' => count($students),
                    ];
                },
                $sections
            );

            $teacherTestsCount = count($this->testsRepository->findByTeacher($user));

        }

        return $this->render('home/index.html.twig', [
            'user' => $user,

            'teacherSections' => $teacherSections,
            'teacherTestsCount' => $teacherTestsCount,
        ]);
    }
}
