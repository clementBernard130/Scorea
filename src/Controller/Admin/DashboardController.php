<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Sections;
use App\Entity\Grades;
use App\Entity\Tests;
use App\Entity\Trainings;
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
        private TrainingsRepository $trainingsRepository
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

        $alerts = $this->generateAlerts($studentCount, $teacherCount, $classCount, $allUsers, $classes);

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

    private function generateAlerts(int $studentCount, int $teacherCount, int $classCount, array $allUsers, array $classes): array
    {
        $alerts = [];

        // A récupérer de la BDD

        return $alerts;
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
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', Users::class)
            ->setController(UserCrudController::class);
        yield MenuItem::linkToCrud('Sections', 'fa fa-chalkboard', Sections::class)
            ->setController(SectionsCrudController::class);

        // Section Formations
        yield MenuItem::section('Formations & Matières', 'fa fa-graduation-cap');
        yield MenuItem::linkToCrud('Formations', 'fa fa-school', Trainings::class)
            ->setController(TrainingCrudController::class);
        yield MenuItem::linkToCrud('Matières', 'fa fa-book', Subjects::class)
            ->setController(SubjectCrudController::class);

        // Section Compétences
        yield MenuItem::section('Compétences', 'fa fa-star');
        yield MenuItem::linkToCrud('Blocs de Compétences', 'fa fa-cubes', SkillsUnit::class)
            ->setController(SkillUnitCrudController::class);

        // Section Évaluations
        yield MenuItem::section('Évaluations', 'fa fa-chart-bar');
        yield MenuItem::linkToCrud('Tests', 'fa fa-clipboard-list', Tests::class)
            ->setController(TestsCrudController::class);
        yield MenuItem::linkToCrud('Notes', 'fa fa-graduation-cap', Grades::class)
            ->setController(GradesCrudController::class);
    }
}
