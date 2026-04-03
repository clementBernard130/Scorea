<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeacherDashboardController extends AbstractController
{
    #[Route('/teacher', name: 'teacher_admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
