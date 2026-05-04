<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Users;
use App\Entity\Trainings;
use App\Entity\Subjects;
use App\Entity\SkillsUnit;
use App\Entity\Skills;
use App\Entity\Grades;
use App\Entity\GradeTypeNames;
use App\Entity\Sections;
use App\Entity\Tests;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // === Trainings ===
        $trainings = [
            'BTS SIO',
            'Bachelor DSNS',
            'Bachelor CYBER',
            'M1 DSNS',
            'M2 DSNS',
            'M1 CYBER',
            'M2 CYBER'
        ];

        foreach ($trainings as $trainingName) {
            $training = new Trainings();
            $training->setName($trainingName);
            $manager->persist($training);
        }

        // === Subjects ===
        $subjects = [
            [
                'name' => 'Communication', 
                'coefficient' => 1
            ],
            [
                'name' => 'Gestion de projet', 
                'coefficient' => 2
            ],
            [
                'name' => 'Cryptographie', 
                'coefficient' => 3
            ],
            [
                'name' => 'Framework PHP', 
                'coefficient' => 3
            ],
            [
                'name' => 'Python', 
                'coefficient' => 3
            ],
            [
                'name' => 'Linux', 
                'coefficient' => 2
            ],
            [
                'name' => 'Base de données', 
                'coefficient' => 3
            ],
            [
                'name' => 'Mémoire d\'entreprise', 
                'coefficient' => 4
            ],
            [
                'name' => 'Anglais', 
                'coefficient' => 1
            ],
            [
                'name' => 'RoR', 
                'coefficient' => 2
            ],
            [
                'name' => 'Projet', 
                'coefficient' => 4
            ]
        ];

        foreach ($subjects as $subjectData) {
            $subject = new Subjects();
            $subject->setName($subjectData['name']);
            $subject->setDescription('Description pour ' . $subjectData['name']);
            $subject->setCoefficient($subjectData['coefficient']);
            $manager->persist($subject);
        }

        // === Admin ===
        $admin = [
            [
                'username' => 'admin1', 
                'first_name' => 'Damien', 
                'last_name' => 'Admin',
                'password' => 'password',
                'email' => 'admin1@example.com'
            ],
            [
                'username' => 'admin2', 
                'first_name' => 'Margaux', 
                'last_name' => 'Admin',
                'password' => 'password',
                'email' => 'admin2@example.com'
            ],
        ];

        foreach ($admin as $adminData) {
            $user = new Users();
            $user->setUsername($adminData['username']);
            $user->setFirstName($adminData['first_name']);
            $user->setLastName($adminData['last_name']);
            $user->setEmail($adminData['email']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $adminData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            $manager->persist($user);
        }

        $manager->flush();

        // === Sections ===
        $sections = [
            [
                'name' => 'SIO1-2026',
                'training' => 'BTS SIO',
            ],
            [
                'name' => 'SIO2-2026',
                'training' => 'BTS SIO',
            ],
            [
                'name' => 'DSNS1-2026',
                'training' => 'Bachelor DSNS',
            ],
            [
                'name' => 'CYBER1-2026',
                'training' => 'Bachelor CYBER',
            ],
            [
                'name' => 'CYBER2-2026',
                'training' => 'Bachelor CYBER',
            ],
            [
                'name' => 'M1-DSNS-2026',
                'training' => 'M1 DSNS',
            ],
            [
                'name' => 'M2-DSNS-2026',
                'training' => 'M2 DSNS',
            ],
            [
                'name' => 'M1-CYBER-2026',
                'training' => 'M1 CYBER',
            ],
            [
                'name' => 'M2-CYBER-2026',
                'training' => 'M2 CYBER',
            ]
        ];

        foreach ($sections as $sectionData) {
            $section = new Sections();
            $section->setName($sectionData['name']);
            $section->setTraining($manager->getRepository(Trainings::class)->findOneBy(['name' => $sectionData['training']]));
            $section->setStartDate(new DateTime('2025-09-01'));
            $section->setEndDate(new DateTime('2026-07-30'));
            $manager->persist($section);
        }

        $manager->flush();

        // === Teachers ===
        $teachers = [
            [
                'username' => 'teacher1', 
                'first_name' => 'Mathias', 
                'last_name' => 'Teacher',
                'email' => 'teacher1@example.com',
                'password' => 'password',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026'
                ],
                'subject' => [
                    'Gestion de projet',
                    'Python',
                    'Framework PHP',
                    'Projet'
                ]
            ],
            [
                'username' => 'teacher2', 
                'first_name' => 'Christophe', 
                'last_name' => 'Teacher',
                'email' => 'teacher2@example.com',
                'password' => 'password',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026'
                ],
                'subject' => [
                    'Base de données',
                    'Linux'
                ]
            ],
            [
                'username' => 'teacher3', 
                'first_name' => 'Anne', 
                'last_name' => 'Teacher',
                'password' => 'password',
                'email' => 'teacher3@example.com',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026'
                ],
                'subject' => [
                    'Cryptographie',
                ]
            ],
            [
                'username' => 'teacher4', 
                'first_name' => 'Antonin', 
                'last_name' => 'Teacher',
                'password' => 'password',
                'email' => 'teacher4@example.com',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026'
                ],
                'subject' => [
                    'Communication',
                ]
            ],
            [
                'username' => 'teacher5', 
                'first_name' => 'Elisa', 
                'last_name' => 'Teacher',
                'email' => 'teacher5@example.com',
                'password' => 'password',
                'section' => [
                    'SIO1-2026',
                    'SIO2-2026',
                    'DSNS1-2026'
                ],
                'subject' => [
                    'Communication',
                    'Anglais'
                ]
            ],
            [
                'username' => 'teacher6', 
                'first_name' => 'Julien', 
                'last_name' => 'Teacher',
                'email' => 'teacher6@example.com',
                'password' => 'password',
                'section' => [
                    'DSNS1-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026',
                    'M1-CYBER-2026'
                ],
                'subject' => [
                    'Base de données',
                    'Linux'
                ]
            ],
            [
                'username' => 'teacher7', 
                'first_name' => 'Sara', 
                'last_name' => 'Teacher',
                'email' => 'teacher7@example.com',
                'password' => 'password',
                'section' => [
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-CYBER-2026',
                    'M2-CYBER-2026'
                ],
                'subject' => [
                    'Framework PHP',
                    'Python',
                    'RoR'
                ]
            ],
            [
                'username' => 'teacher8', 
                'first_name' => 'Karim', 
                'last_name' => 'Teacher',
                'email' => 'teacher8@example.com',
                'password' => 'password',
                'section' => [
                    'M1-DSNS-2026',
                    'M2-DSNS-2026',
                    'M1-CYBER-2026',
                    'M2-CYBER-2026'
                ],
                'subject' => [
                    'Gestion de projet',
                    'Mémoire d\'entreprise'
                ]
            ]
        ];

        foreach ($teachers as $teacherData) {
            $user = new Users();
            $user->setUsername($teacherData['username']);
            $user->setFirstName($teacherData['first_name']);
            $user->setLastName($teacherData['last_name']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $teacherData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_TEACHER']);
            $user->setEmail($teacherData['email']);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            foreach ($teacherData['section'] as $sectionName) {
                $user->addSection($manager->getRepository(Sections::class)->findOneBy(['name' => $sectionName]));
            }
            foreach ($teacherData['subject'] as $subjectName) {
                $subject = $manager->getRepository(Subjects::class)->findOneBy(['name' => $subjectName]);
                $subject->addTeacher($user);
            }
            $manager->persist($user);
        }

        // === Students ===
        $students = [
            [
                'username' => 'student1', 
                'first_name' => 'Aymeric', 
                'last_name' => 'Student1',
                'password' => 'password',
                'section' => 'DSNS1-2026',
                'email' => 'student1@example.com'
            ],
            [
                'username' => 'student2', 
                'first_name' => 'Enzo', 
                'last_name' => 'Student2',
                'password' => 'password',
                'section' => 'DSNS1-2026',
                'email' => 'student2@example.com'
            ],
            [
                'username' => 'student3', 
                'first_name' => 'Clément', 
                'last_name' => 'Student3',
                'password' => 'password',
                'section' => 'DSNS1-2026',
                'email' => 'student3@example.com'
            ],
            [
                'username' => 'student4', 
                'first_name' => 'Philippe', 
                'last_name' => 'Student4',
                'password' => 'password',
                'section' => 'DSNS1-2026',
                'email' => 'student4@example.com'
            ],
            [
                'username' => 'student5', 
                'first_name' => 'Valentin', 
                'last_name' => 'Student5',
                'password' => 'password',
                'section' => 'CYBER1-2026',
                'email' => 'student5@example.com'
            ],
            [
                'username' => 'student6', 
                'first_name' => 'Aymeric', 
                'last_name' => 'Student6',
                'password' => 'password',
                'section' => 'CYBER1-2026',
                'email' => 'student6@example.com'
            ],
            [
                'username' => 'student7', 
                'first_name' => 'François-Clément', 
                'last_name' => 'Student7',
                'password' => 'password',
                'section' => 'CYBER1-2026',
                'email' => 'student7@example.com'
            ],
            [
                'username' => 'student8', 
                'first_name' => 'Paul', 
                'last_name' => 'Student8',
                'password' => 'password',
                'section' => 'CYBER1-2026',
                'email' => 'student8@example.com'
            ],
            [
                'username' => 'student9', 
                'first_name' => 'Sophie', 
                'last_name' => 'Student9',
                'password' => 'password',
                'section' => 'M1-DSNS-2026',
                'email' => 'student9@example.com'
            ],
            [
                'username' => 'student10', 
                'first_name' => 'Camille', 
                'last_name' => 'Student10',
                'password' => 'password',
                'section' => 'M1-DSNS-2026',
                'email' => 'student10@example.com'
            ],
            [
                'username' => 'student11', 
                'first_name' => 'Alexandre', 
                'last_name' => 'Student11',
                'password' => 'password',
                'section' => 'M1-DSNS-2026',
                'email' => 'student11@example.com'
            ],
            [
                'username' => 'student12', 
                'first_name' => 'Marie', 
                'last_name' => 'Student12',
                'password' => 'password',
                'section' => 'M1-DSNS-2026',
                'email' => 'student12@example.com'
            ],
            [
                'username' => 'student13', 
                'first_name' => 'Léandre', 
                'last_name' => 'Student13',
                'password' => 'password',
                'section' => 'M2-DSNS-2026',
                'email' => 'student13@example.com'
            ],
            [
                'username' => 'student14', 
                'first_name' => 'Julie', 
                'last_name' => 'Student14',
                'password' => 'password',
                'section' => 'SIO1-2026',
                'email' => 'student14@example.com'
            ],
            [
                'username' => 'student15', 
                'first_name' => 'Thomas', 
                'last_name' => 'Student15',
                'password' => 'password',
                'section' => 'SIO1-2026',
                'email' => 'student15@example.com'
            ],
            [
                'username' => 'student16', 
                'first_name' => 'Sarah', 
                'last_name' => 'Student16',
                'password' => 'password',
                'section' => 'SIO1-2026',
                'email' => 'student16@example.com'
            ],
            [
                'username' => 'student17', 
                'first_name' => 'Nicolas', 
                'last_name' => 'Student17',
                'password' => 'password',
                'section' => 'SIO1-2026',
                'email' => 'student17@example.com'
            ],
            [
                'username' => 'student18', 
                'first_name' => 'Emma', 
                'last_name' => 'Student18',
                'password' => 'password',
                'section' => 'SIO2-2026',
                'email' => 'student18@example.com'
            ],
            [
                'username' => 'student19', 
                'first_name' => 'Lucas', 
                'last_name' => 'Student19',
                'password' => 'password',
                'section' => 'SIO2-2026',
                'email' => 'student19@example.com'
            ],
            [
                'username' => 'student20', 
                'first_name' => 'Inès', 
                'last_name' => 'Student20',
                'password' => 'password',
                'section' => 'SIO2-2026',
                'email' => 'student20@example.com'
            ],
            [
                'username' => 'student21', 
                'first_name' => 'Hugo', 
                'last_name' => 'Student21',
                'password' => 'password',
                'section' => 'SIO2-2026',
                'email' => 'student21@example.com'
            ],
            [
                'username' => 'student22', 
                'first_name' => 'Chloé', 
                'last_name' => 'Student22',
                'password' => 'password',
                'section' => 'CYBER2-2026',
                'email' => 'student22@example.com'
            ],
            [
                'username' => 'student23', 
                'first_name' => 'Mathis', 
                'last_name' => 'Student23',
                'password' => 'password',
                'section' => 'CYBER2-2026',
                'email' => 'student23@example.com'
            ],
            [
                'username' => 'student24', 
                'first_name' => 'Lina', 
                'last_name' => 'Student24',
                'password' => 'password',
                'section' => 'CYBER2-2026',
                'email' => 'student24@example.com'
            ],
            [
                'username' => 'student25', 
                'first_name' => 'Ethan', 
                'last_name' => 'Student25',
                'password' => 'password',
                'section' => 'CYBER2-2026',
                'email' => 'student25@example.com'
            ],
            [
                'username' => 'student26', 
                'first_name' => 'Manon', 
                'last_name' => 'Student26',
                'password' => 'password',
                'section' => 'M1-CYBER-2026',
                'email' => 'student26@example.com'
            ],
            [
                'username' => 'student27', 
                'first_name' => 'Romain', 
                'last_name' => 'Student27',
                'password' => 'password',
                'section' => 'M1-CYBER-2026',
                'email' => 'student27@example.com'
            ],
            [
                'username' => 'student28', 
                'first_name' => 'Alicia', 
                'last_name' => 'Student28',
                'password' => 'password',
                'section' => 'M1-CYBER-2026',
                'email' => 'student28@example.com'
            ],
            [
                'username' => 'student29', 
                'first_name' => 'Yanis', 
                'last_name' => 'Student29',
                'password' => 'password',
                'section' => 'M1-CYBER-2026',
                'email' => 'student29@example.com'
            ],
            [
                'username' => 'student30', 
                'first_name' => 'Mélanie', 
                'last_name' => 'Student30',
                'password' => 'password',
                'section' => 'M2-CYBER-2026',
                'email' => 'student30@example.com'
            ],
            [
                'username' => 'student31', 
                'first_name' => 'Adrien', 
                'last_name' => 'Student31',
                'password' => 'password',
                'section' => 'M2-CYBER-2026',
                'email' => 'student31@example.com'
            ],
            [
                'username' => 'student32', 
                'first_name' => 'Léa', 
                'last_name' => 'Student32',
                'password' => 'password',
                'section' => 'M2-CYBER-2026',
                'email' => 'student32@example.com'
            ],
            [
                'username' => 'student33', 
                'first_name' => 'Baptiste', 
                'last_name' => 'Student33',
                'password' => 'password',
                'section' => 'M2-CYBER-2026',
                'email' => 'student33@example.com'
            ],
            [
                'username' => 'student34', 
                'first_name' => 'Camille', 
                'last_name' => 'Student34',
                'password' => 'password',
                'section' => 'M2-DSNS-2026',
                'email' => 'student34@example.com'
            ],
            [
                'username' => 'student35', 
                'first_name' => 'Noah', 
                'last_name' => 'Student35',
                'password' => 'password',
                'section' => 'M2-DSNS-2026',
                'email' => 'student35@example.com'
            ],
            [
                'username' => 'student36', 
                'first_name' => 'Zoé', 
                'last_name' => 'Student36',
                'password' => 'password',
                'section' => 'M2-DSNS-2026',
                'email' => 'student36@example.com'
            ]
        ];

        foreach ($students as $studentData) {
            $user = new Users();
            $user->setUsername($studentData['username']);
            $user->setFirstName($studentData['first_name']);
            $user->setLastName($studentData['last_name']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $studentData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_STUDENT']);
            $user->setEmail($studentData['email']);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            $user->addSection($manager->getRepository(Sections::class)->findOneBy(['name' => $studentData['section']]));
            $manager->persist($user);
        }

        $manager->flush();

        // === Skills Unit ===
        $skillUnits = [
            [
                'name' => 'SIO-BLOC-1 : Maintenir les infrastructures et services',
                'training' => 'BTS SIO',
            ],
            [
                'name' => 'SIO-BLOC-2 : Développer des applications et gérer les données',
                'training' => 'BTS SIO',
            ],
            [
                'name' => 'SIO-BLOC-3 : Piloter un projet et accompagner les utilisateurs',
                'training' => 'BTS SIO',
            ],
            [
                'name' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution',
                'training' => 'Bachelor DSNS',
            ],
            [
                'name' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution',
                'training' => 'Bachelor DSNS',
            ],
            [
                'name' => 'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de modernisation de solution',
                'training' => 'Bachelor DSNS',
            ],
            [
                'name' => 'CYBER-BLOC-1 : Analyser les risques et définir la gouvernance SSI',
                'training' => 'Bachelor CYBER',
            ],
            [
                'name' => 'CYBER-BLOC-2 : Mettre en oeuvre des mécanismes de protection',
                'training' => 'Bachelor CYBER',
            ],
            [
                'name' => 'CYBER-BLOC-3 : Détecter, investiguer et répondre aux incidents',
                'training' => 'Bachelor CYBER',
            ],
            [
                'name' => 'M1-DSNS-BLOC-1 : Concevoir des architectures cloud et data',
                'training' => 'M1 DSNS',
            ],
            [
                'name' => 'M1-DSNS-BLOC-2 : Industrialiser la qualité logicielle et la sécurité',
                'training' => 'M1 DSNS',
            ],
            [
                'name' => 'M2-DSNS-BLOC-1 : Piloter la transformation et la gouvernance SI',
                'training' => 'M2 DSNS',
            ],
            [
                'name' => 'M2-DSNS-BLOC-2 : Superviser des plateformes distribuées critiques',
                'training' => 'M2 DSNS',
            ],
            [
                'name' => 'M1-CYBER-BLOC-1 : Réaliser des audits techniques et pentests',
                'training' => 'M1 CYBER',
            ],
            [
                'name' => 'M1-CYBER-BLOC-2 : Concevoir des architectures sécurisées',
                'training' => 'M1 CYBER',
            ],
            [
                'name' => 'M2-CYBER-BLOC-1 : Orchestrer un SOC et la threat intelligence',
                'training' => 'M2 CYBER',
            ],
            [
                'name' => 'M2-CYBER-BLOC-2 : Piloter la conformité et la résilience cyber',
                'training' => 'M2 CYBER',
            ],
        ];

        foreach ($skillUnits as $skillUnitData) {
            $training = $manager->getRepository(Trainings::class)->findOneBy(['name' => $skillUnitData['training']]);

            $skillUnit = new SkillsUnit();
            $skillUnit->setName($skillUnitData['name']);
            $skillUnit->setDescription('Description pour ' . $skillUnitData['name']);
            $skillUnit->setTrainings($training);
            $manager->persist($skillUnit);
        }

        $manager->flush();

        // === Skills ===
        $skills = [
            [
                'name' => 'Administrer un parc Linux et Windows',
                'description' => 'Gérer les postes, droits et services techniques en environnement mixte',
                'skillUnit' => 'SIO-BLOC-1 : Maintenir les infrastructures et services',
            ],
            [
                'name' => 'Superviser un réseau local',
                'description' => 'Diagnostiquer les incidents et garantir la continuité de service',
                'skillUnit' => 'SIO-BLOC-1 : Maintenir les infrastructures et services',
            ],
            [
                'name' => 'Développer une application CRUD sécurisée',
                'description' => 'Concevoir des fonctionnalités métier avec authentification et validation',
                'skillUnit' => 'SIO-BLOC-2 : Développer des applications et gérer les données',
            ],
            [
                'name' => 'Modéliser une base de données relationnelle',
                'description' => 'Construire un schéma cohérent et écrire des requêtes SQL optimisées',
                'skillUnit' => 'SIO-BLOC-2 : Développer des applications et gérer les données',
            ],
            [
                'name' => 'Recueillir le besoin utilisateur',
                'description' => 'Animer des échanges avec le client et formaliser les attentes',
                'skillUnit' => 'SIO-BLOC-3 : Piloter un projet et accompagner les utilisateurs',
            ],
            [
                'name' => 'Planifier un projet applicatif',
                'description' => 'Découper les tâches, suivre les jalons et communiquer avec les parties prenantes',
                'skillUnit' => 'SIO-BLOC-3 : Piloter un projet et accompagner les utilisateurs',
            ],
            [
                'name' => 'Analyser les besoins métiers',
                'description' => 'Capacité à analyser et comprendre les besoins métiers',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution',
            ],
            [
                'name' => 'Concevoir une architecture applicative',
                'description' => 'Définir l\'architecture technique d\'une application',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution',
            ],
            [
                'name' => 'Sécuriser une application',
                'description' => 'Mettre en place des mesures de sécurité applicative',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution',
            ],
            [
                'name' => 'Développer des composants métier',
                'description' => 'Créer des composants logiciels réutilisables',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution',
            ],
            [
                'name' => 'Mettre en production une application',
                'description' => 'Déployer et configurer une application en production',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution',
            ],
            [
                'name' => 'Maintenir une application',
                'description' => 'Assurer la maintenance corrective et évolutive',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution',
            ],
            [
                'name' => 'Gérer un projet de modernisation',
                'description' => 'Piloter un projet de migration technologique',
                'skillUnit' => 'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de modernisation de solution',
            ],
            [
                'name' => 'Refactoriser du code legacy',
                'description' => 'Améliorer du code existant sans changer son comportement',
                'skillUnit' => 'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de modernisation de solution',
            ],
            [
                'name' => 'Cartographier les risques SSI',
                'description' => 'Identifier les menaces et prioriser les mesures de sécurité',
                'skillUnit' => 'CYBER-BLOC-1 : Analyser les risques et définir la gouvernance SSI',
            ],
            [
                'name' => 'Définir une politique de sécurité',
                'description' => 'Rédiger les règles de gouvernance, d\'accès et de conformité',
                'skillUnit' => 'CYBER-BLOC-1 : Analyser les risques et définir la gouvernance SSI',
            ],
            [
                'name' => 'Déployer des mécanismes de chiffrement',
                'description' => 'Mettre en oeuvre le chiffrement des données au repos et en transit',
                'skillUnit' => 'CYBER-BLOC-2 : Mettre en oeuvre des mécanismes de protection',
            ],
            [
                'name' => 'Durcir un système Linux',
                'description' => 'Appliquer des mesures de hardening et réduire la surface d\'attaque',
                'skillUnit' => 'CYBER-BLOC-2 : Mettre en oeuvre des mécanismes de protection',
            ],
            [
                'name' => 'Analyser des logs de sécurité',
                'description' => 'Corréler des événements pour détecter des comportements anormaux',
                'skillUnit' => 'CYBER-BLOC-3 : Détecter, investiguer et répondre aux incidents',
            ],
            [
                'name' => 'Gérer un plan de réponse à incident',
                'description' => 'Qualifier, contenir et traiter un incident de sécurité',
                'skillUnit' => 'CYBER-BLOC-3 : Détecter, investiguer et répondre aux incidents',
            ],
            [
                'name' => 'Concevoir une architecture cloud multi-tier',
                'description' => 'Déployer des services découplés et résilients sur plusieurs couches',
                'skillUnit' => 'M1-DSNS-BLOC-1 : Concevoir des architectures cloud et data',
            ],
            [
                'name' => 'Construire des pipelines de données',
                'description' => 'Collecter, transformer et servir des jeux de données fiables',
                'skillUnit' => 'M1-DSNS-BLOC-1 : Concevoir des architectures cloud et data',
            ],
            [
                'name' => 'Automatiser les tests et la CI/CD',
                'description' => 'Mettre en place des workflows de qualité et de livraison continue',
                'skillUnit' => 'M1-DSNS-BLOC-2 : Industrialiser la qualité logicielle et la sécurité',
            ],
            [
                'name' => 'Appliquer le security-by-design',
                'description' => 'Intégrer les exigences de sécurité dès la conception',
                'skillUnit' => 'M1-DSNS-BLOC-2 : Industrialiser la qualité logicielle et la sécurité',
            ],
            [
                'name' => 'Piloter un portefeuille de projets numériques',
                'description' => 'Arbitrer la priorisation, les budgets et la valeur métier',
                'skillUnit' => 'M2-DSNS-BLOC-1 : Piloter la transformation et la gouvernance SI',
            ],
            [
                'name' => 'Définir une gouvernance de la donnée',
                'description' => 'Structurer la qualité, les responsabilités et les règles d\'usage de la donnée',
                'skillUnit' => 'M2-DSNS-BLOC-1 : Piloter la transformation et la gouvernance SI',
            ],
            [
                'name' => 'Superviser une plateforme distribuée',
                'description' => 'Mettre en place des indicateurs, alertes et capacités de scaling',
                'skillUnit' => 'M2-DSNS-BLOC-2 : Superviser des plateformes distribuées critiques',
            ],
            [
                'name' => 'Concevoir une stratégie de haute disponibilité',
                'description' => 'Garantir la continuité de service et la reprise après sinistre',
                'skillUnit' => 'M2-DSNS-BLOC-2 : Superviser des plateformes distribuées critiques',
            ],
            [
                'name' => 'Réaliser un test d\'intrusion web',
                'description' => 'Identifier et exploiter des failles applicatives de manière contrôlée',
                'skillUnit' => 'M1-CYBER-BLOC-1 : Réaliser des audits techniques et pentests',
            ],
            [
                'name' => 'Auditer la configuration d\'un SI',
                'description' => 'Évaluer les configurations réseau, système et IAM',
                'skillUnit' => 'M1-CYBER-BLOC-1 : Réaliser des audits techniques et pentests',
            ],
            [
                'name' => 'Modéliser une architecture Zero Trust',
                'description' => 'Segmenter les accès et imposer une authentification forte continue',
                'skillUnit' => 'M1-CYBER-BLOC-2 : Concevoir des architectures sécurisées',
            ],
            [
                'name' => 'Sécuriser un cycle DevSecOps',
                'description' => 'Intégrer la détection de vulnérabilités dans la chaîne de build',
                'skillUnit' => 'M1-CYBER-BLOC-2 : Concevoir des architectures sécurisées',
            ],
            [
                'name' => 'Orchestrer un SOC de niveau 2',
                'description' => 'Coordonner la détection, l\'escalade et l\'investigation avancée',
                'skillUnit' => 'M2-CYBER-BLOC-1 : Orchestrer un SOC et la threat intelligence',
            ],
            [
                'name' => 'Produire des analyses de threat intelligence',
                'description' => 'Qualifier des TTP et anticiper les campagnes malveillantes',
                'skillUnit' => 'M2-CYBER-BLOC-1 : Orchestrer un SOC et la threat intelligence',
            ],
            [
                'name' => 'Piloter un plan de conformité ISO 27001',
                'description' => 'Structurer les contrôles et la documentation de conformité',
                'skillUnit' => 'M2-CYBER-BLOC-2 : Piloter la conformité et la résilience cyber',
            ],
            [
                'name' => 'Définir une stratégie de cyber-résilience',
                'description' => 'Anticiper les crises et organiser les exercices de continuité',
                'skillUnit' => 'M2-CYBER-BLOC-2 : Piloter la conformité et la résilience cyber',
            ],
        ];

        foreach ($skills as $skillData) {
            $skill = new Skills();
            $skill->setName($skillData['name']);
            $skill->setDescription($skillData['description']);
            $skill->setSkillUnit($manager->getRepository(SkillsUnit::class)->findOneBy(['name' => $skillData['skillUnit']]));
            $manager->persist($skill);
        }

        $manager->flush();

        // === Lier Skills aux Subjects ===
        $pythonSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Python']);
        $frameworkSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Framework PHP']);
        $bddSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Base de données']);
        $linuxSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Linux']);
        $anglaisSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Anglais']);
        $projetSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Projet']);
        
        $analyserSkill = $manager->getRepository(Skills::class)->findOneBy(['name' => 'Analyser les besoins métiers']);
        $concevoirSkill = $manager->getRepository(Skills::class)->findOneBy(['name' => 'Concevoir une architecture applicative']);
        $securiserSkill = $manager->getRepository(Skills::class)->findOneBy(['name' => 'Sécuriser une application']);
        $developerSkill = $manager->getRepository(Skills::class)->findOneBy(['name' => 'Développer des composants métier']);
        $productionSkill = $manager->getRepository(Skills::class)->findOneBy(['name' => 'Mettre en production une application']);
        
        $pythonSubject->addSkill($developerSkill);
        $frameworkSubject->addSkill($developerSkill);
        $frameworkSubject->addSkill($concevoirSkill);
        $bddSubject->addSkill($concevoirSkill);
        $bddSubject->addSkill($securiserSkill);
        $projetSubject->addSkill($analyserSkill);
        $projetSubject->addSkill($concevoirSkill);
        $projetSubject->addSkill($productionSkill);
        
        $manager->flush();

        // === Tests ===
        $tests = [
            ['code' => 'python_dsns1_cc1', 'subject' => $pythonSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher1', 'comment' => 'Controle Python - structures de donnees', 'testDate' => new DateTime('2026-01-15'), 'gradingMode' => 'full', 'coverage' => 1.0],
            ['code' => 'python_dsns1_quiz2', 'subject' => $pythonSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher1', 'comment' => 'Quiz Python - algorithmie', 'testDate' => new DateTime('2026-02-12'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'bdd_dsns1_cc1', 'subject' => $bddSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher2', 'comment' => 'Controle Base de donnees - SQL avance', 'testDate' => new DateTime('2026-03-10'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'framework_dsns1_cc1', 'subject' => $frameworkSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher1', 'comment' => 'Controle Framework PHP - Symfony', 'testDate' => new DateTime('2026-02-20'), 'gradingMode' => 'none', 'coverage' => 0.0],
            ['code' => 'framework_dsns1_tp2', 'subject' => $frameworkSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher1', 'comment' => 'TP Framework - validation de formulaire', 'testDate' => new DateTime('2026-04-06'), 'gradingMode' => 'partial', 'coverage' => 0.5],
            ['code' => 'projet_dsns1_rev1', 'subject' => $projetSubject, 'section' => 'DSNS1-2026', 'teacher' => 'teacher1', 'comment' => 'Revue de sprint projet', 'testDate' => new DateTime('2026-05-10'), 'gradingMode' => 'full', 'coverage' => 1.0],

            ['code' => 'anglais_sio1_cc1', 'subject' => $anglaisSubject, 'section' => 'SIO1-2026', 'teacher' => 'teacher5', 'comment' => 'Evaluation orale Anglais', 'testDate' => new DateTime('2026-01-21'), 'gradingMode' => 'full', 'coverage' => 1.0],
            ['code' => 'anglais_sio1_cc2', 'subject' => $anglaisSubject, 'section' => 'SIO1-2026', 'teacher' => 'teacher5', 'comment' => 'Listening Anglais', 'testDate' => new DateTime('2026-03-21'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'projet_sio1_rev1', 'subject' => $projetSubject, 'section' => 'SIO1-2026', 'teacher' => 'teacher5', 'comment' => 'Point projet de groupe', 'testDate' => new DateTime('2026-04-17'), 'gradingMode' => 'partial', 'coverage' => 0.75],

            ['code' => 'anglais_sio2_cc1', 'subject' => $anglaisSubject, 'section' => 'SIO2-2026', 'teacher' => 'teacher5', 'comment' => 'Evaluation orale Anglais', 'testDate' => new DateTime('2026-01-25'), 'gradingMode' => 'full', 'coverage' => 1.0],
            ['code' => 'bdd_sio2_cc1', 'subject' => $bddSubject, 'section' => 'SIO2-2026', 'teacher' => 'teacher6', 'comment' => 'Requetes SQL complexes', 'testDate' => new DateTime('2026-03-01'), 'gradingMode' => 'partial', 'coverage' => 0.5],
            ['code' => 'projet_sio2_rev1', 'subject' => $projetSubject, 'section' => 'SIO2-2026', 'teacher' => 'teacher5', 'comment' => 'Revue projet - API REST', 'testDate' => new DateTime('2026-04-25'), 'gradingMode' => 'partial', 'coverage' => 0.75],

            ['code' => 'python_cyber1_cc1', 'subject' => $pythonSubject, 'section' => 'CYBER1-2026', 'teacher' => 'teacher7', 'comment' => 'Scripts Python pour securite', 'testDate' => new DateTime('2026-02-03'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'crypto_cyber1_cc1', 'subject' => $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Cryptographie']), 'section' => 'CYBER1-2026', 'teacher' => 'teacher3', 'comment' => 'Principes de chiffrement', 'testDate' => new DateTime('2026-03-14'), 'gradingMode' => 'full', 'coverage' => 1.0],
            ['code' => 'projet_cyber1_rev1', 'subject' => $projetSubject, 'section' => 'CYBER1-2026', 'teacher' => 'teacher7', 'comment' => 'Revue projet SSI', 'testDate' => new DateTime('2026-05-08'), 'gradingMode' => 'partial', 'coverage' => 0.5],

            ['code' => 'framework_cyber2_cc1', 'subject' => $frameworkSubject, 'section' => 'CYBER2-2026', 'teacher' => 'teacher7', 'comment' => 'Securisation Symfony', 'testDate' => new DateTime('2026-02-15'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'crypto_cyber2_cc1', 'subject' => $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Cryptographie']), 'section' => 'CYBER2-2026', 'teacher' => 'teacher3', 'comment' => 'Protocoles cryptographiques', 'testDate' => new DateTime('2026-03-16'), 'gradingMode' => 'full', 'coverage' => 1.0],
            ['code' => 'linux_cyber2_tp1', 'subject' => $linuxSubject, 'section' => 'CYBER2-2026', 'teacher' => 'teacher6', 'comment' => 'Durcissement Linux', 'testDate' => new DateTime('2026-04-22'), 'gradingMode' => 'none', 'coverage' => 0.0],

            ['code' => 'linux_m1dsns_tp1', 'subject' => $linuxSubject, 'section' => 'M1-DSNS-2026', 'teacher' => 'teacher6', 'comment' => 'TP Linux - administration systeme', 'testDate' => new DateTime('2026-02-18'), 'gradingMode' => 'none', 'coverage' => 0.0],
            ['code' => 'bdd_m1dsns_cc1', 'subject' => $bddSubject, 'section' => 'M1-DSNS-2026', 'teacher' => 'teacher6', 'comment' => 'Data modelisation avancee', 'testDate' => new DateTime('2026-03-26'), 'gradingMode' => 'partial', 'coverage' => 0.5],
            ['code' => 'projet_m1dsns_rev1', 'subject' => $projetSubject, 'section' => 'M1-DSNS-2026', 'teacher' => 'teacher1', 'comment' => 'Revue architecture projet', 'testDate' => new DateTime('2026-05-05'), 'gradingMode' => 'full', 'coverage' => 1.0],

            ['code' => 'projet_m2dsns_soutenance_blanc', 'subject' => $projetSubject, 'section' => 'M2-DSNS-2026', 'teacher' => 'teacher1', 'comment' => 'Soutenance projet blanc', 'testDate' => new DateTime('2026-02-28'), 'gradingMode' => 'none', 'coverage' => 0.0],
            ['code' => 'framework_m2dsns_cc1', 'subject' => $frameworkSubject, 'section' => 'M2-DSNS-2026', 'teacher' => 'teacher1', 'comment' => 'Architecture Symfony distribuee', 'testDate' => new DateTime('2026-03-30'), 'gradingMode' => 'partial', 'coverage' => 0.5],
            ['code' => 'bdd_m2dsns_cc1', 'subject' => $bddSubject, 'section' => 'M2-DSNS-2026', 'teacher' => 'teacher2', 'comment' => 'Scalabilite et performance SQL', 'testDate' => new DateTime('2026-05-15'), 'gradingMode' => 'full', 'coverage' => 1.0],

            ['code' => 'python_m1cyber_cc1', 'subject' => $pythonSubject, 'section' => 'M1-CYBER-2026', 'teacher' => 'teacher7', 'comment' => 'Python offensif et defensive', 'testDate' => new DateTime('2026-02-10'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'framework_m1cyber_cc1', 'subject' => $frameworkSubject, 'section' => 'M1-CYBER-2026', 'teacher' => 'teacher7', 'comment' => 'Securisation API web', 'testDate' => new DateTime('2026-03-20'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'crypto_m1cyber_cc1', 'subject' => $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Cryptographie']), 'section' => 'M1-CYBER-2026', 'teacher' => 'teacher3', 'comment' => 'PKI et signatures numeriques', 'testDate' => new DateTime('2026-04-29'), 'gradingMode' => 'full', 'coverage' => 1.0],

            ['code' => 'linux_m2cyber_tp1', 'subject' => $linuxSubject, 'section' => 'M2-CYBER-2026', 'teacher' => 'teacher6', 'comment' => 'Forensic Linux', 'testDate' => new DateTime('2026-02-14'), 'gradingMode' => 'partial', 'coverage' => 0.5],
            ['code' => 'projet_m2cyber_rev1', 'subject' => $projetSubject, 'section' => 'M2-CYBER-2026', 'teacher' => 'teacher7', 'comment' => 'Revue de posture de securite', 'testDate' => new DateTime('2026-03-27'), 'gradingMode' => 'partial', 'coverage' => 0.75],
            ['code' => 'crypto_m2cyber_cc1', 'subject' => $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Cryptographie']), 'section' => 'M2-CYBER-2026', 'teacher' => 'teacher3', 'comment' => 'Cryptanalyse appliquee', 'testDate' => new DateTime('2026-05-19'), 'gradingMode' => 'full', 'coverage' => 1.0],
        ];

        $testsByCode = [];
        $testsMetadata = [];
        foreach ($tests as $testData) {
            $section = $manager->getRepository(Sections::class)->findOneBy(['name' => $testData['section']]);
            $teacher = $manager->getRepository(Users::class)->findOneBy(['username' => $testData['teacher']]);

            $test = new Tests();
            $test->setSubject($testData['subject']);
            $test->setSection($section);
            $test->setTeacher($teacher);
            $test->setComment($testData['comment']);
            $test->setTestDate($testData['testDate']);
            $manager->persist($test);
            $testsByCode[$testData['code']] = $test;
            $testsMetadata[$testData['code']] = [
                'section' => $testData['section'],
                'gradingMode' => $testData['gradingMode'],
                'coverage' => $testData['coverage'],
            ];
        }

        $manager->flush();

        // === Grades ===
        $gradeType = $manager->getRepository(GradeTypeNames::class)->findOneBy(['name' => 'Contrôle continu']);
        if ($gradeType === null) {
            $gradeType = new GradeTypeNames();
            $gradeType->setName('Contrôle continu');
            $gradeType->setIsCertificative(false);
            $manager->persist($gradeType);
            $manager->flush();
        }

        $studentsBySection = [];
        foreach (['SIO1-2026', 'SIO2-2026', 'DSNS1-2026', 'CYBER1-2026', 'CYBER2-2026', 'M1-DSNS-2026', 'M2-DSNS-2026', 'M1-CYBER-2026', 'M2-CYBER-2026'] as $sectionName) {
            $section = $manager->getRepository(Sections::class)->findOneBy(['name' => $sectionName]);

            $students = [];
            foreach ($section->getUsers() as $user) {
                if (in_array('ROLE_STUDENT', $user->getRoles(), true)) {
                    $students[] = $user;
                }
            }

            $studentsBySection[$sectionName] = $students;
        }

        $testIndex = 0;
        foreach ($testsByCode as $testCode => $test) {
            $metadata = $testsMetadata[$testCode];
            $gradingMode = $metadata['gradingMode'];
            if ($gradingMode === 'none') {
                $testIndex++;
                continue;
            }

            $students = $studentsBySection[$metadata['section']];
            foreach ($students as $studentIndex => $student) {
                $shouldCreateGrade = $gradingMode === 'full';
                if ($gradingMode === 'partial') {
                    $ratio = ($studentIndex + 1) / max(1, count($students));
                    $offset = fmod($testIndex * 0.13, 0.35);
                    $shouldCreateGrade = $ratio <= min(1.0, $metadata['coverage'] + $offset);
                }

                if (!$shouldCreateGrade) {
                    continue;
                }

                $rawGrade = 9.0 + fmod(($studentIndex * 1.7) + ($testIndex * 1.15), 10.6);
                $computedGrade = round(min(19.5, $rawGrade), 1);

                $grade = new Grades();
                $grade->setStudent($student);
                $grade->setTest($test);
                $grade->setGradeType($gradeType);
                $grade->setGrade($computedGrade);
                if ($computedGrade >= 15.0) {
                    $grade->setComment('Tres bon niveau sur cette evaluation');
                } elseif ($computedGrade < 10.0) {
                    $grade->setComment('Consolider les acquis est recommande');
                }
                $grade->setCreatedAt(new DateTimeImmutable());
                $grade->setUpdatedAt(new DateTimeImmutable());
                $manager->persist($grade);
            }

            $testIndex++;
        }

        $manager->flush();
    }
}
