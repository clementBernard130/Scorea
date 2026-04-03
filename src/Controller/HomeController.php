<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\StudentSkillsPageBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
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

        return $this->render('home/index.html.twig', [
            'user' => $user,
        ]);
    }
}
