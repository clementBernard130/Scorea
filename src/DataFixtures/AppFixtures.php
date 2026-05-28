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
use App\Entity\GradeTypes;
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
            "Bachelor DSNS"
        ];

        foreach($trainings as $trainingName) {
            $training = new Trainings();
            $training->setName($trainingName);
            $manager->persist($training);
        }

        // === Subjects ===
        $subjects = [
            [
                'name' => 'Communication', 
                'description' => 'Cours de communication',
                'coefficient' => 1
            ],
            [
                'name' => 'Gestion de projet',
                'description' => 'Gestion de projet informatique',
                'coefficient' => 2
            ],
            [
                'name' => 'Cryptographie', 
                'description' => 'Cours de cryptographie',
                'coefficient' => 3
            ],
            [
                'name' => 'Framework PHP', 
                'description' => 'Langage : Symfony',
                'coefficient' => 3
            ],
            [
                'name' => 'Python', 
                'description' => 'Langage : Python',
                'coefficient' => 3
            ],
            [
                'name' => 'Linux', 
                'description' => 'Administration système Linux',
                'coefficient' => 2
            ],
            [
                'name' => 'Base de données', 
                'description' => 'SQL, NoSQL, etc.',
                'coefficient' => 3
            ],
            [
                'name' => 'Mémoire d\'entreprise',
                'description' => 'Rédaction du mémoire d\'entreprise',
                'coefficient' => 4
            ],
            [
                'name' => 'Anglais', 
                'description' => 'Cours d\'anglais',
                'coefficient' => 1
            ],
            [
                'name' => 'RoR', 
                'description' => 'D\'après certains professeurs, ce serait le meilleur langage...',
                'coefficient' => 2
            ],
            [
                'name' => 'Projet', 
                'description' => 'Le fameux projet de fin d\'année',
                'coefficient' => 4
            ]
        ];

        foreach ($subjects as $subjectData) {
            $subject = new Subjects();
            $subject->setName($subjectData['name']);
            $subject->setDescription($subjectData['description']);
            $subject->setCoefficient($subjectData['coefficient']);
            $manager->persist($subject);
        }

        // === Admin
        $admin = [
            [
                'username' => 'damien.admin',
                'first_name' => 'Damien', 
                'last_name' => 'Admin',
                'password' => 'scoreapassword',
                'email' => 'admin1@example.com'
            ]
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

        // === Section ===

        $sections = [
            [
                'name' => 'DSNS-1-2026',
                'training' => 'Bachelor DSNS'
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
                'username' => 'mathias.teacher', 
                'first_name' => 'Mathias', 
                'last_name' => 'Teacher',
                'email' => 'teacher1@example.com',
                'password' => 'scoreapassword',
                'section' => [
                    'DSNS-1-2026',
                ],
                'subject' => [
                    'Gestion de projet',
                    'Python',
                    'Framework PHP',
                    'Projet'
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

        $manager->flush();

        // === Students ===
        $students = [
            [
                'username' => 'aymeric.student', 
                'first_name' => 'Aymeric', 
                'last_name' => 'Student1',
                'password' => 'scoreapassword',
                'section' => 'DSNS-1-2026',
                'email' => 'student1@example.com'
            ],
            [
                'username' => 'enzo.student', 
                'first_name' => 'Enzo', 
                'last_name' => 'Student2',
                'password' => 'scoreapassword',
                'section' => 'DSNS-1-2026',
                'email' => 'student2@example.com'
            ],
            [
                'username' => 'clement.student', 
                'first_name' => 'Clément', 
                'last_name' => 'Student3',
                'password' => 'scoreapassword',
                'section' => 'DSNS-1-2026',
                'email' => 'student3@example.com'
            ],
            [
                'username' => 'philippe.student', 
                'first_name' => 'Philippe', 
                'last_name' => 'Student4',
                'password' => 'scoreapassword',
                'section' => 'DSNS-1-2026',
                'email' => 'student4@example.com'
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

        // === SkillsUnit ===
        $skillUnits = [
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
            ]
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
        ];

        foreach ($skills as $skillData) {
            $skill = new Skills();
            $skill->setName($skillData['name']);
            $skill->setDescription($skillData['description']);
            $skill->setSkillUnit($manager->getRepository(SkillsUnit::class)->findOneBy(['name' => $skillData['skillUnit']]));
            $manager->persist($skill);
        }

        $manager->flush();

        // === Grade type names ===
        $gradeTypeNamesByName = [];
        foreach (['Contrôle Continu', 'Oral Projet', 'DS'] as $gradeTypeName) {
            $gradeTypeEntity = $manager->getRepository(GradeTypeNames::class)->findOneBy(['name' => $gradeTypeName]);
            if ($gradeTypeEntity === null) {
                $gradeTypeEntity = new GradeTypeNames();
                $gradeTypeEntity->setName($gradeTypeName);
                $manager->persist($gradeTypeEntity);
            }

            $gradeTypeNamesByName[$gradeTypeName] = $gradeTypeEntity;
        }

        $manager->flush();

        // === Grade types and weights by skill ===
        $skillGradeTypes = [
            'Analyser les besoins métiers' => [
                'Contrôle Continu' => 30,
                'Oral Projet' => 20,
                'DS' => 50,
            ],
            'Concevoir une architecture applicative' => [
                'Contrôle Continu' => 40,
                'Oral Projet' => 20,
                'DS' => 40,
            ],
            'Sécuriser une application' => [
                'Contrôle Continu' => 35,
                'Oral Projet' => 15,
                'DS' => 50,
            ],
            'Développer des composants métier' => [
                'Contrôle Continu' => 45,
                'Oral Projet' => 15,
                'DS' => 40,
            ],
            'Mettre en production une application' => [
                'Contrôle Continu' => 30,
                'Oral Projet' => 20,
                'DS' => 50,
            ],
            'Maintenir une application' => [
                'Contrôle Continu' => 40,
                'Oral Projet' => 20,
                'DS' => 40,
            ],
            'Gérer un projet de modernisation' => [
                'Contrôle Continu' => 25,
                'Oral Projet' => 25,
                'DS' => 50,
            ],
            'Refactoriser du code legacy' => [
                'Contrôle Continu' => 35,
                'Oral Projet' => 25,
                'DS' => 40,
            ],
        ];

        foreach ($skillGradeTypes as $skillName => $gradeTypesWithWeights) {
            $skill = $manager->getRepository(Skills::class)->findOneBy(['name' => $skillName]);

            if ($skill === null) {
                throw new \RuntimeException(sprintf('Compétence "%s" introuvable pour les types d\'épreuves.', $skillName));
            }

            foreach ($gradeTypesWithWeights as $gradeTypeName => $weight) {
                $gradeType = new GradeTypes();
                $gradeType->setSkill($skill);
                $gradeType->setType($gradeTypeNamesByName[$gradeTypeName]);
                $gradeType->setWeight($weight);
                $manager->persist($gradeType);
            }
        }

        $manager->flush();

        // Lier skills au subjects
        $pythonSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Python']);
        $frameworkSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Framework PHP']);
        $bddSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Base de données']);
        $linuxSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Linux']);
        $anglaisSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Anglais']);
        $projetSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Projet']);
        $gestionDeProjetSubject = $manager->getRepository(Subjects::class)->findOneBy(['name' => 'Gestion de projet']);
        
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
        $gestionDeProjetSubject->addSkill($analyserSkill);
        $gestionDeProjetSubject->addSkill($concevoirSkill);

        $manager->flush();

        // === Tests ===
        $tests = [
            [
                'code' => 'python_dsns1_cc1', 
                'subject' => $pythonSubject, 
                'section' => 'DSNS-1-2026', 
                'teacher' => 'mathias.teacher', 
                'comment' => 'Controle Python - structures de donnees', 
                'testDate' => new DateTime('2026-01-15'), 
                'gradeType' => 'Contrôle Continu',
                'gradingMode' => 'full', 
                'coverage' => 1.0
            ],
            [
                'code' => 'python_dsns1_quiz2', 
                'subject' => $pythonSubject, 
                'section' => 'DSNS-1-2026', 
                'teacher' => 'mathias.teacher', 
                'comment' => 'Quiz Python - algorithmie', 
                'testDate' => new DateTime('2026-02-12'), 
                'gradeType' => 'Oral Projet',
                'gradingMode' => 'none', 
                'coverage' => 0.75
            ],
            [
                'code' => 'framework_dsns1_cc1', 
                'subject' => $frameworkSubject, 
                'section' => 'DSNS-1-2026', 
                'teacher' => 'mathias.teacher', 
                'comment' => 'Controle Framework PHP - Symfony', 
                'testDate' => new DateTime('2026-02-20'), 
                'gradeType' => 'Contrôle Continu',
                'gradingMode' => 'none', 
                'coverage' => 0.0
            ],
            [
                'code' => 'framework_dsns1_tp2', 
                'subject' => $frameworkSubject, 
                'section' => 'DSNS-1-2026', 
                'teacher' => 'mathias.teacher', 
                'comment' => 'TP Framework - validation de formulaire', 
                'testDate' => new DateTime('2026-04-06'), 
                'gradeType' => 'Oral Projet',
                'gradingMode' => 'partial', 
                'coverage' => 0.5
            ],
            [
                'code' => 'gestionprojet_dsns_eval',
                'subject' => $gestionDeProjetSubject,
                'section' => 'DSNS-1-2026',
                'teacher' => 'mathias.teacher',
                'comment' => 'Evaluation finale de gestion de projet',
                'testDate' => new DateTime('2026-06-10'),
                'gradeType' => 'DS',
                'gradingMode' => 'full',
                'coverage' => 1.0
            ]
        ];

        $testsByCode = [];
        $testsMetadata = [];
        foreach ($tests as $testData) {
            $section = $manager->getRepository(Sections::class)->findOneBy(['name' => $testData['section']]);
            $teacher = $manager->getRepository(Users::class)->findOneBy(['username' => $testData['teacher']]);
            $gradeType = $gradeTypeNamesByName[$testData['gradeType']] ?? null;

            if ($section === null) {
                throw new \RuntimeException(sprintf('Section "%s" introuvable pour le test "%s".', $testData['section'], $testData['code']));
            }

            if ($teacher === null) {
                throw new \RuntimeException(sprintf('Teacher "%s" introuvable pour le test "%s".', $testData['teacher'], $testData['code']));
            }

            if ($gradeType === null) {
                throw new \RuntimeException(sprintf('Type d\'épreuve "%s" introuvable pour le test "%s".', $testData['gradeType'], $testData['code']));
            }

            $test = new Tests();
            $test->setSubject($testData['subject']);
            $test->setSection($section);
            $test->setTeacher($teacher);
            $test->setComment($testData['comment']);
            $test->setTestDate($testData['testDate']);
            $test->setGradeType($gradeType);
            $test->setIsCertificative($testData['gradingMode'] !== 'none');
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
        $studentsByUsername = [];
        foreach (['aymeric.student', 'enzo.student', 'clement.student', 'philippe.student'] as $studentUsername) {
            $student = $manager->getRepository(Users::class)->findOneBy(['username' => $studentUsername]);
            if ($student === null) {
                throw new \RuntimeException(sprintf('Student "%s" introuvable pour les notes.', $studentUsername));
            }
            $studentsByUsername[$studentUsername] = $student;
        }

        $gradesData = [
                ['test' => 'framework_dsns1_cc1', 'student' => 'aymeric.student', 'grade' => 19.0, 'comment' => 'Bonne comprehension de Symfony.'],
                ['test' => 'framework_dsns1_cc1', 'student' => 'enzo.student', 'grade' => 15.5, 'comment' => 'Bon travail, mais quelques erreurs dans la configuration.'],
                ['test' => 'framework_dsns1_cc1', 'student' => 'clement.student', 'grade' => 12.0, 'comment' => 'Des difficultés à comprendre les concepts de base.'],
                ['test' => 'framework_dsns1_cc1', 'student' => 'philippe.student', 'grade' => 14.0, 'comment' => 'OK. Quelques erreurs, mais une bonne compréhension générale.'],
                ['test' => 'python_dsns1_cc1', 'student' => 'aymeric.student', 'grade' => 18.0, 'comment' => 'Excellente maîtrise des structures de données en Python.'],
                ['test' => 'python_dsns1_cc1', 'student' => 'enzo.student', 'grade' => 14.0, 'comment' => 'Bonne compréhension générale, mais quelques erreurs dans les exercices pratiques.'],
                ['test' => 'python_dsns1_cc1', 'student' => 'clement.student', 'grade' => 10.0, 'comment' => 'Difficultés à appliquer les concepts appris en cours.'],
                ['test' => 'python_dsns1_cc1', 'student' => 'philippe.student', 'grade' => 16.5, 'comment' => 'Ok. Quelques erreurs.'],
        ];

        foreach ($gradesData as $gradeData) {
            $test = $testsByCode[$gradeData['test']] ?? null;
            $grade = new Grades();
            $grade->setTest($test);
            $grade->setStudent($studentsByUsername[$gradeData['student']]);
            $grade->setGrade($gradeData['grade']);
            $grade->setComment($gradeData['comment']);
            $grade->setCreatedAt(new DateTimeImmutable());
            $grade->setUpdatedAt(new DateTimeImmutable());
            $manager->persist($grade);
        }

        $manager->flush();

        
    }
}
