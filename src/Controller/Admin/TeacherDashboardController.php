<?php

namespace App\Controller\Admin;

use App\Entity\Grades;
use App\Entity\Subjects;
use App\Entity\Users;
use App\Controller\Admin\TeacherGradesCrudController;
use App\Controller\Admin\TeacherStudentCrudController;
use App\Controller\Admin\TeacherSubjectCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/teacher', routeName: 'teacher_admin')]
class TeacherDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Scorea - Espace Enseignant');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Matières', 'fa fa-book');
        yield MenuItem::linkToCrud('Matières', 'fa fa-book', Subjects::class)
            ->setController(TeacherSubjectCrudController::class);

        yield MenuItem::section('Élèves', 'fa fa-users');
        yield MenuItem::linkToCrud('Mes élèves', 'fa fa-user-graduate', Users::class)
            ->setController(TeacherStudentCrudController::class);

        yield MenuItem::section('Évaluations', 'fa fa-chart-bar');
        yield MenuItem::linkToRoute('Tests', 'fa fa-clipboard-list', 'teacher_portal_tests_index');
        yield MenuItem::linkToCrud('Notes', 'fa fa-graduation-cap', Grades::class)
            ->setController(TeacherGradesCrudController::class);
    }
}
