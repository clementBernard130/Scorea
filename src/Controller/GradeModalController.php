<?php

namespace App\Controller;

use App\Entity\Grades;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GradeModalController extends AbstractController
{
    #[Route('/grade/{id}/details', name: 'grade_details')]
    public function details(Grades $grade): Response
    {
        return $this->render('student/grades/modal_details.html.twig', [
            'grade' => $grade,
        ]);
    }
}