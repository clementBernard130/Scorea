<?php

namespace App\Controller;

use App\Entity\Grades;
use App\Entity\Sections;
use App\Entity\Tests;
use App\Entity\Users;
use App\Repository\GradeTypeNamesRepository;
use App\Repository\GradesRepository;
use App\Repository\SectionsRepository;
use App\Repository\TestsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherPortalController extends AbstractController
{
    public function __construct(
        private UsersRepository $usersRepository,
        private GradeTypeNamesRepository $gradeTypeNamesRepository,
        private GradesRepository $gradesRepository,
        private SectionsRepository $sectionsRepository,
        private TestsRepository $testsRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/teacher-portal/tests', name: 'teacher_portal_tests_index')]
    public function testsIndex(Request $request): Response
    {
        $teacher = $this->getTeacherUser();
        $section = $this->resolveSectionFromRequest($request, $teacher);
        $tests = $this->testsRepository->findByTeacher($teacher);

        $studentsForContext = $section !== null
            ? $this->usersRepository->findStudentsBySection($section)
            : $this->getTeacherStudents($teacher);
        $studentIdsForContext = array_values(array_filter(array_map(
            static fn (Users $student): ?int => $student->getId(),
            $studentsForContext
        )));

        $canAddGradeByTestId = [];
        foreach ($tests as $test) {
            $testId = $test->getId();
            if ($testId === null) {
                continue;
            }

            $gradedStudentIds = $this->gradesRepository->findStudentIdsForTest($test);
            $remainingStudentIds = array_diff($studentIdsForContext, $gradedStudentIds);
            $canAddGradeByTestId[$testId] = count($remainingStudentIds) > 0;
        }

        return $this->render('teacher/tests_index.html.twig', [
            'tests' => $tests,
            'section' => $section,
            'canAddGradeByTestId' => $canAddGradeByTestId,
        ]);
    }

    #[Route('/teacher-portal/tests/new', name: 'teacher_portal_test_new', methods: ['GET', 'POST'])]
    public function newTest(Request $request): Response
    {
        $teacher = $this->getTeacherUser();
        $section = $this->resolveSectionFromRequest($request, $teacher);

        $test = new Tests();
        $test->setTeacher($teacher);
        $test->setTestDate(new \DateTime());

        $form = $this->createFormBuilder($test)
            ->add('subject', EntityType::class, [
                'class' => \App\Entity\Subjects::class,
                'choice_label' => 'name',
                'label' => 'Matière',
            ])
            ->add('testDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du test',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($test);
            $this->entityManager->flush();

            if ($section !== null) {
                return $this->redirectToRoute('app_class_show', ['id' => $section->getId()]);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('teacher/test_form.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'pageTitle' => 'Créer une évaluation',
            'submitLabel' => 'Créer le test',
            'formAction' => $this->generateUrl('teacher_portal_test_new', $section ? ['section' => $section->getId()] : []),
        ]);
    }

    #[Route('/teacher-portal/tests/{id}/edit', name: 'teacher_portal_test_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function editTest(Request $request, Tests $test): Response
    {
        $teacher = $this->getTeacherUser();
        if ($test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Accès refusé à cette évaluation.');
        }

        $section = $this->resolveSectionFromRequest($request, $teacher);

        $form = $this->createFormBuilder($test)
            ->add('subject', EntityType::class, [
                'class' => \App\Entity\Subjects::class,
                'choice_label' => 'name',
                'label' => 'Matière',
            ])
            ->add('testDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du test',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            if ($section !== null) {
                return $this->redirectToRoute('app_class_show', ['id' => $section->getId()]);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('teacher/test_form.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'pageTitle' => 'Modifier une évaluation',
            'submitLabel' => 'Enregistrer',
            'formAction' => $this->generateUrl('teacher_portal_test_edit', $section ? ['id' => $test->getId(), 'section' => $section->getId()] : ['id' => $test->getId()]),
        ]);
    }

    #[Route('/teacher-portal/tests/{id}/grades/new', name: 'teacher_portal_grade_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function newGrade(Request $request, Tests $test): Response
    {
        $teacher = $this->getTeacherUser();
        if ($test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Accès refusé à cette évaluation.');
        }

        $section = $this->resolveSectionFromRequest($request, $teacher);
        $students = $section !== null
            ? $this->usersRepository->findStudentsBySection($section)
            : $this->getTeacherStudents($teacher);
        $availableStudents = $this->filterStudentsNotYetGradedForTest($test, $students);

        $grade = new Grades();
        $grade->setTest($test);

        $form = $this->createFormBuilder($grade)
            ->add('student', EntityType::class, [
                'class' => Users::class,
                'choices' => $availableStudents,
                'choice_label' => static function (Users $user): string {
                    $fullName = trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));

                    return $fullName !== '' ? $fullName : (string) $user->getUsername();
                },
                'label' => 'Élève',
            ])
            ->add('gradeType', EntityType::class, [
                'class' => \App\Entity\GradeTypeNames::class,
                'choice_label' => 'name',
                'choices' => $this->gradeTypeNamesRepository->findBy([], ['name' => 'ASC']),
                'label' => 'Type de note',
            ])
            ->add('grade', NumberType::class, [
                'label' => 'Note',
                'scale' => 2,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedStudent = $grade->getStudent();
            if ($selectedStudent instanceof Users && $this->gradesRepository->studentAlreadyGradedForTest($test, $selectedStudent)) {
                $form->get('student')->addError(new FormError('Cet étudiant a déjà une note pour ce test.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $grade->setCreatedAt($now);
            $grade->setUpdatedAt($now);

            $this->entityManager->persist($grade);
            $this->entityManager->flush();

            if ($section !== null) {
                return $this->redirectToRoute('app_class_show', ['id' => $section->getId()]);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('teacher/grade_form.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'test' => $test,
            'pageTitle' => 'Saisir une note',
            'submitLabel' => 'Enregistrer la note',
            'formAction' => $this->generateUrl('teacher_portal_grade_new', $section ? ['id' => $test->getId(), 'section' => $section->getId()] : ['id' => $test->getId()]),
        ]);
    }

    #[Route('/teacher-portal/grades/{id}/edit', name: 'teacher_portal_grade_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function editGrade(Request $request, Grades $grade): Response
    {
        $teacher = $this->getTeacherUser();
        $test = $grade->getTest();
        if ($test === null || $test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Accès refusé à cette note.');
        }

        $section = $this->resolveSectionFromRequest($request, $teacher);
        $students = $section !== null
            ? $this->usersRepository->findStudentsBySection($section)
            : $this->getTeacherStudents($teacher);
        $availableStudents = $this->filterStudentsNotYetGradedForTest($test, $students, $grade);

        $form = $this->createFormBuilder($grade)
            ->add('student', EntityType::class, [
                'class' => Users::class,
                'choices' => $availableStudents,
                'choice_label' => static function (Users $user): string {
                    $fullName = trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));

                    return $fullName !== '' ? $fullName : (string) $user->getUsername();
                },
                'label' => 'Élève',
            ])
            ->add('gradeType', EntityType::class, [
                'class' => \App\Entity\GradeTypeNames::class,
                'choice_label' => 'name',
                'choices' => $this->gradeTypeNamesRepository->findBy([], ['name' => 'ASC']),
                'label' => 'Type de note',
            ])
            ->add('grade', NumberType::class, [
                'label' => 'Note',
                'scale' => 2,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedStudent = $grade->getStudent();
            if ($selectedStudent instanceof Users && $this->gradesRepository->studentAlreadyGradedForTest($test, $selectedStudent, $grade->getId())) {
                $form->get('student')->addError(new FormError('Cet étudiant a déjà une note pour ce test.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $grade->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            if ($section !== null) {
                return $this->redirectToRoute('app_class_show', ['id' => $section->getId()]);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('teacher/grade_form.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'test' => $test,
            'pageTitle' => 'Modifier une note',
            'submitLabel' => 'Enregistrer les modifications',
            'formAction' => $this->generateUrl('teacher_portal_grade_edit', $section ? ['id' => $grade->getId(), 'section' => $section->getId()] : ['id' => $grade->getId()]),
        ]);
    }

    #[Route('/teacher-portal/grades/{id}/delete', name: 'teacher_portal_grade_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function deleteGrade(Request $request, Grades $grade): Response
    {
        $teacher = $this->getTeacherUser();
        $test = $grade->getTest();

        if ($test === null || $test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Accès refusé à cette note.');
        }

        if (!$this->isCsrfTokenValid('delete_grade_'.$grade->getId(), (string) $request->request->get('_token'))) {
            throw new AccessDeniedException('Jeton CSRF invalide.');
        }

        $section = $this->resolveSectionFromRequest($request, $teacher);
        $testId = $test->getId();

        $this->entityManager->remove($grade);
        $this->entityManager->flush();

        if ($testId === null) {
            return $this->redirectToRoute('teacher_portal_tests_index');
        }

        return $this->redirectToRoute('app_test_show', $section ? ['id' => $testId, 'section' => $section->getId()] : ['id' => $testId]);
    }

    #[Route('/teacher-portal/tests/{id}/delete', name: 'teacher_portal_test_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function deleteTest(Request $request, Tests $test): Response
    {
        $teacher = $this->getTeacherUser();
        if ($test->getTeacher()?->getId() !== $teacher->getId()) {
            throw new AccessDeniedException('Accès refusé à cette évaluation.');
        }

        if (!$this->isCsrfTokenValid('delete_test_'.$test->getId(), (string) $request->request->get('_token'))) {
            throw new AccessDeniedException('Jeton CSRF invalide.');
        }

        $section = $this->resolveSectionFromRequest($request, $teacher);

        foreach ($test->getGrades() as $grade) {
            $this->entityManager->remove($grade);
        }

        $this->entityManager->remove($test);
        $this->entityManager->flush();

        if ($section !== null) {
            return $this->redirectToRoute('app_class_show', ['id' => $section->getId()]);
        }

        return $this->redirectToRoute('teacher_portal_tests_index');
    }

    private function getTeacherStudents(Users $teacher): array
    {
        $studentIds = $this->usersRepository->findStudentIdsForTeacher($teacher);
        if ($studentIds === []) {
            return [];
        }

        $students = $this->usersRepository->findBy(['id' => $studentIds]);

        usort($students, static function (Users $a, Users $b): int {
            $aLastName = (string) $a->getLastName();
            $bLastName = (string) $b->getLastName();

            $byLastName = strcasecmp($aLastName, $bLastName);
            if ($byLastName !== 0) {
                return $byLastName;
            }

            return strcasecmp((string) $a->getFirstName(), (string) $b->getFirstName());
        });

        return $students;
    }

    /**
     * @param Users[] $students
     * @return Users[]
     */
    private function filterStudentsNotYetGradedForTest(Tests $test, array $students, ?Grades $currentGrade = null): array
    {
        $gradedStudentIds = $this->gradesRepository->findStudentIdsForTest($test);

        $currentStudentId = $currentGrade?->getStudent()?->getId();
        if ($currentStudentId !== null) {
            $gradedStudentIds = array_values(array_filter(
                $gradedStudentIds,
                static fn (int $studentId): bool => $studentId !== $currentStudentId
            ));
        }

        $gradedStudentIdsLookup = array_fill_keys($gradedStudentIds, true);

        return array_values(array_filter(
            $students,
            static function (Users $student) use ($gradedStudentIdsLookup): bool {
                $studentId = $student->getId();
                if ($studentId === null) {
                    return false;
                }

                return !isset($gradedStudentIdsLookup[$studentId]);
            }
        ));
    }

    private function resolveSectionFromRequest(Request $request, Users $teacher): ?Sections
    {
        $sectionId = $request->query->getInt('section');
        if ($sectionId <= 0) {
            return null;
        }

        $section = $this->sectionsRepository->find($sectionId);
        if (!$section instanceof Sections) {
            return null;
        }

        if (!$teacher->getSections()->contains($section)) {
            throw new AccessDeniedException('Accès refusé à cette classe.');
        }

        return $section;
    }

    private function getTeacherUser(): Users
    {
        $user = $this->getUser();
        if (!$user instanceof Users || !in_array('ROLE_TEACHER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Accès réservé aux enseignants.');
        }

        return $user;
    }
}