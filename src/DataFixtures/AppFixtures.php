<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Training;
use App\Entity\Subject;
use App\Entity\SkillUnit;
use App\Entity\Skill;
use App\Entity\Grades;
use App\Entity\Sections;
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
            $training = new Training();
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
            $subject = new Subject();
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
                'password' => 'adminpass',
            ],
            [
                'username' => 'admin2', 
                'first_name' => 'Margaux', 
                'last_name' => 'Admin',
                'password' => 'adminpass',
            ],
        ];

        foreach ($admin as $adminData) {
            $user = new User();
            $user->setUsername($adminData['username']);
            $user->setFirstName($adminData['first_name']);
            $user->setLastName($adminData['last_name']);
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
            $section->setTrainingId($manager->getRepository(Training::class)->findOneBy(['name' => $sectionData['training']]));
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
                'password' => 'teacherpass',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026'
                ]
            ],
            [
                'username' => 'teacher2', 
                'first_name' => 'Christophe', 
                'last_name' => 'Teacher',
                'password' => 'teacherpass',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026',
                    'M1-DSNS-2026',
                    'M2-DSNS-2026'
                ]
            ],
            [
                'username' => 'teacher3', 
                'first_name' => 'Anne', 
                'last_name' => 'Teacher',
                'password' => 'teacherpass',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026'
                ]
            ],
            [
                'username' => 'teacher4', 
                'first_name' => 'Antonin', 
                'last_name' => 'Teacher',
                'password' => 'teacherpass',
                'section' => [
                    'DSNS1-2026',
                    'CYBER1-2026',
                    'CYBER2-2026'
                ]
            ]
        ];

        foreach ($teachers as $teacherData) {
            $user = new User();
            $user->setUsername($teacherData['username']);
            $user->setFirstName($teacherData['first_name']);
            $user->setLastName($teacherData['last_name']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $teacherData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_TEACHER']);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            foreach ($teacherData['section'] as $sectionName) {
                $user->addSection($manager->getRepository(Sections::class)->findOneBy(['name' => $sectionName]));
            }
            $manager->persist($user);
        }

        // === Students ===
        $students = [
            [
                'username' => 'student1', 
                'first_name' => 'Aymeric', 
                'last_name' => 'Student',
                'password' => 'studentpass',
                'section' => 'DSNS1-2026',
        ],
            [
                'username' => 'student2', 
                'first_name' => 'Enzo', 
                'last_name' => 'Student',
                'password' => 'studentpass',
                'section' => 'DSNS1-2026',
            ],
            [
                'username' => 'student3', 
                'first_name' => 'Clément', 
                'last_name' => 'Student',
                'password' => 'studentpass',
                'section' => 'DSNS1-2026',
            ],
            [
                'username' => 'student4', 
                'first_name' => 'Philippe', 
                'last_name' => 'Student',
                'password' => 'studentpass',
                'section' => 'DSNS1-2026',
            ]
        ];

        foreach ($students as $studentData) {
            $user = new User();
            $user->setUsername($studentData['username']);
            $user->setFirstName($studentData['first_name']);
            $user->setLastName($studentData['last_name']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $studentData['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_STUDENT']);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            $user->addSection($manager->getRepository(Sections::class)->findOneBy(['name' => $studentData['section']]));
            $manager->persist($user);
        }

        $manager->flush();

        // === Skill Unit ===
        $skillUnits = [
            'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution',
            'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution',
            'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de mondernisation de solution'
        ];

        foreach ($skillUnits as $skillUnitName) {
            $skillUnit = new SkillUnit();
            $skillUnit->setName($skillUnitName);
            $skillUnit->setDescription('Description pour ' . $skillUnitName);
            $manager->persist($skillUnit);
        }

        $manager->flush();

        // === Skills ===
        $skills = [
            [
                'name' => 'Analyser les besoins métiers',
                'description' => 'Capacité à analyser et comprendre les besoins métiers',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution'
            ],
            [
                'name' => 'Concevoir une architecture applicative',
                'description' => 'Définir l\'architecture technique d\'une application',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution'
            ],
            [
                'name' => 'Sécuriser une application',
                'description' => 'Mettre en place des mesures de sécurité applicative',
                'skillUnit' => 'DSNS-BLOC-1 : Analyser, conceptualiser, maquetter et sécuriser une solution'
            ],
            [
                'name' => 'Développer des composants métier',
                'description' => 'Créer des composants logiciels réutilisables',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution'
            ],
            [
                'name' => 'Mettre en production une application',
                'description' => 'Déployer et configurer une application en production',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution'
            ],
            [
                'name' => 'Maintenir une application',
                'description' => 'Assurer la maintenance corrective et évolutive',
                'skillUnit' => 'DSNS-BLOC-2 : Concevoir, développer, mettre en production et maintenir une solution'
            ],
            [
                'name' => 'Gérer un projet de modernisation',
                'description' => 'Piloter un projet de migration technologique',
                'skillUnit' => 'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de mondernisation de solution'
            ],
            [
                'name' => 'Refactoriser du code legacy',
                'description' => 'Améliorer du code existant sans changer son comportement',
                'skillUnit' => 'DSNS-BLOC-3 : Concevoir, mettre en oeuvre un projet de mondernisation de solution'
            ]
        ];

        foreach ($skills as $skillData) {
            $skill = new Skill();
            $skill->setName($skillData['name']);
            $skill->setDescription($skillData['description']);
            $skill->setSkillUnit($manager->getRepository(SkillUnit::class)->findOneBy(['name' => $skillData['skillUnit']]));
            $manager->persist($skill);
        }

        $manager->flush();

        // === Lier Skills aux Subjects ===
        $pythonSubject = $manager->getRepository(Subject::class)->findOneBy(['name' => 'Python']);
        $frameworkSubject = $manager->getRepository(Subject::class)->findOneBy(['name' => 'Framework PHP']);
        $bddSubject = $manager->getRepository(Subject::class)->findOneBy(['name' => 'Base de données']);
        $projetSubject = $manager->getRepository(Subject::class)->findOneBy(['name' => 'Projet']);
        
        $analyserSkill = $manager->getRepository(Skill::class)->findOneBy(['name' => 'Analyser les besoins métiers']);
        $concevoirSkill = $manager->getRepository(Skill::class)->findOneBy(['name' => 'Concevoir une architecture applicative']);
        $securiserSkill = $manager->getRepository(Skill::class)->findOneBy(['name' => 'Sécuriser une application']);
        $developerSkill = $manager->getRepository(Skill::class)->findOneBy(['name' => 'Développer des composants métier']);
        $productionSkill = $manager->getRepository(Skill::class)->findOneBy(['name' => 'Mettre en production une application']);
        
        $pythonSubject->addSkill($developerSkill);
        $frameworkSubject->addSkill($developerSkill);
        $frameworkSubject->addSkill($concevoirSkill);
        $bddSubject->addSkill($concevoirSkill);
        $bddSubject->addSkill($securiserSkill);
        $projetSubject->addSkill($analyserSkill);
        $projetSubject->addSkill($concevoirSkill);
        $projetSubject->addSkill($productionSkill);
        
        $manager->flush();

        // === Grades ===     
        $student1 = $manager->getRepository(User::class)->findOneBy(['username' => 'student1']);
        $student2 = $manager->getRepository(User::class)->findOneBy(['username' => 'student2']);
        $student3 = $manager->getRepository(User::class)->findOneBy(['username' => 'student3']);
        $student4 = $manager->getRepository(User::class)->findOneBy(['username' => 'student4']);
        
        $teacher1 = $manager->getRepository(User::class)->findOneBy(['username' => 'teacher1']);
        $teacher2 = $manager->getRepository(User::class)->findOneBy(['username' => 'teacher2']);

        $grades = [
            // Notes pour student1
            ['student' => $student1, 'subject' => $pythonSubject, 'teacher' => $teacher1, 'grade' => 15.5],
            ['student' => $student1, 'subject' => $frameworkSubject, 'teacher' => $teacher1, 'grade' => 14.0],
            ['student' => $student1, 'subject' => $bddSubject, 'teacher' => $teacher2, 'grade' => 16.5],
            
            // Notes pour student2
            ['student' => $student2, 'subject' => $pythonSubject, 'teacher' => $teacher1, 'grade' => 12.0],
            ['student' => $student2, 'subject' => $frameworkSubject, 'teacher' => $teacher1, 'grade' => 13.5],
            ['student' => $student2, 'subject' => $bddSubject, 'teacher' => $teacher2, 'grade' => 11.0],
            
            // Notes pour student3
            ['student' => $student3, 'subject' => $pythonSubject, 'teacher' => $teacher1, 'grade' => 17.0],
            ['student' => $student3, 'subject' => $frameworkSubject, 'teacher' => $teacher1, 'grade' => 15.5],
            ['student' => $student3, 'subject' => $bddSubject, 'teacher' => $teacher2, 'grade' => 18.0],
            
            // Notes pour student4
            ['student' => $student4, 'subject' => $pythonSubject, 'teacher' => $teacher1, 'grade' => 10.5],
            ['student' => $student4, 'subject' => $frameworkSubject, 'teacher' => $teacher1, 'grade' => 12.0],
            ['student' => $student4, 'subject' => $bddSubject, 'teacher' => $teacher2, 'grade' => 13.0],
        ];

        foreach ($grades as $gradeData) {
            $grade = new Grades();
            $grade->setStudent($gradeData['student']);
            $grade->setSubject($gradeData['subject']);
            $grade->setTeacher($gradeData['teacher']);
            $grade->setGrade($gradeData['grade']);
            $manager->persist($grade);
        }

        $manager->flush();
    }
}