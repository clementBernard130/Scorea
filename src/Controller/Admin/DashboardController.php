<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Classe;
use App\Entity\Grade;
use App\Entity\Skill;
use App\Entity\SkillUnit;
use App\Entity\Subject;
use App\Entity\Training;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ProjetFinCCI');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Menu CRUD
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Classes', 'fa fa-chalkboard', Classe::class);
        yield MenuItem::linkToCrud('Grades', 'fa fa-graduation-cap', Grade::class);
        yield MenuItem::linkToCrud('Skills', 'fa fa-lightbulb', Skill::class);
        yield MenuItem::linkToCrud('Skill Units', 'fa fa-cubes', SkillUnit::class);
        yield MenuItem::linkToCrud('Subjects', 'fa fa-book', Subject::class);
        yield MenuItem::linkToCrud('Trainings', 'fa fa-school', Training::class);
    }
}