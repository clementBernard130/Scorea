<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Sections;
use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Trainings;
use App\Repository\AlertsRepository;
use App\Repository\UsersRepository;
use App\Repository\SectionsRepository;
use App\Repository\TrainingsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UsersRepository $userRepository,
        private SectionsRepository $sectionsRepository,
        private TrainingsRepository $trainingsRepository,
        private AlertsRepository $alertsRepository
    ) {}

    public function index(): Response 
    {
        $allUsers = $this->userRepository->findAll();

        $studentCount = count(array_filter($allUsers, function($user) {
            $roles = $user->getRoles();
            return !in_array('ROLE_TEACHER', $roles) && !in_array('ROLE_ADMIN', $roles);
        }));

        $teacherCount = count(array_filter($allUsers, function($user) {
            return in_array('ROLE_TEACHER', $user->getRoles());
        }));

        $classes = $this->sectionsRepository->findAll();
        $classCount = count($classes);

        $trainings = $this->trainingsRepository->findAll();
        $trainingCount = count($trainings);

        $alerts = $this->alertsRepository->findAll();

        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formattedDate = $formatter->format(new \DateTime());

        $currentUser = $this->getUser();
        
        return $this->render('admin/dashboard.html.twig', [
            'currentUser' => $currentUser,
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'classCount' => $classCount,
            'trainingCount' => $trainingCount,
            'formattedDate' => $formattedDate,
            'recentAlerts' => $alerts,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Scorea - Administration')
            ->disableDarkMode()
            ->setDefaultColorScheme(ColorScheme::LIGHT);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Section Gestion des Utilisateurs
        yield MenuItem::section('Gestion des Utilisateurs', 'fa fa-users');
        yield MenuItem::linkToCrud('Administrateurs', 'fa fa-user-shield', Users::class)
            ->setController(AdminCrudController::class);
        yield MenuItem::linkToCrud('Professeurs', 'fa fa-chalkboard-user', Users::class)
            ->setController(TeacherCrudController::class);
        yield MenuItem::linkToCrud('Étudiants', 'fa fa-user-graduate', Users::class)
            ->setController(StudentCrudController::class);

        // Section Formations
        yield MenuItem::section('Formations', 'fa fa-graduation-cap');
        yield MenuItem::linkToCrud('Sections', 'fa fa-chalkboard', Sections::class);
        yield MenuItem::linkToCrud('Formations', 'fa fa-school', Trainings::class);

        // Section Synchronisation
        yield MenuItem::section('Synchronisation', 'fa fa-file-arrow-up');
        yield MenuItem::linkToRoute('Données étudiants & formations', 'fa fa-file-import', 'admin_import_export');
    }
}
