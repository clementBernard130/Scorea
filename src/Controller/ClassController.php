<?php

namespace App\Controller;

use App\Entity\Sections;
use App\Entity\Users;
use App\Repository\TestsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ClassController extends AbstractController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private TestsRepository $testsRepository
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

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();

        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }
}