<?php

namespace App\Controller;

use App\Entity\Grades;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GradeModalController extends AbstractController
{
    #[Route('/grade/{id}/details', name: 'grade_details')]
    public function details(Grades $grade): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            throw new AccessDeniedException('Acces refuse.');
        }

        $isOwner = $grade->getStudent()?->getId() === $user->getId();
        if (!$isOwner && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException('Acces refuse a cette note.');
        }

        return $this->render('student/grades/modal_details.html.twig', [
            'grade' => $grade,
        ]);
    }
}