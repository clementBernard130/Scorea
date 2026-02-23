<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Sections;
use App\Entity\Grades;
use App\Entity\Skills;
use App\Entity\SkillsUnit;
use App\Entity\Subjects;
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
        yield MenuItem::linkToCrud('Users', 'fa fa-user', Users::class);
        yield MenuItem::linkToCrud('Sections', 'fa fa-chalkboard', Sections::class);
        yield MenuItem::linkToCrud('Grades', 'fa fa-graduation-cap', Grades::class);
        yield MenuItem::linkToCrud('Skills', 'fa fa-lightbulb', Skills::class);
        yield MenuItem::linkToCrud('Skills Unit', 'fa fa-cubes', SkillsUnit::class);
        yield MenuItem::linkToCrud('Subjects', 'fa fa-book', Subjects::class);
        yield MenuItem::linkToCrud('Trainings', 'fa fa-school', Training::class);
    }
}
