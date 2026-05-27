<?php

namespace App\Controller\Student;

use App\Entity\Users;
use App\Repository\GradesRepository;
use App\Service\EvaluationHistoryPresenter;
use App\Service\StudentSkillGradeResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class EvaluationHistoryController extends AbstractController
{
    #[Route('/student/evaluations/history', name: 'student_evaluation_history', methods: ['GET'])]
    public function index(
        Request $request,
        GradesRepository $gradesRepository,
        StudentSkillGradeResolver $studentSkillGradeResolver,
        EvaluationHistoryPresenter $presenter,
    ): Response {
        $student = $this->getUser();
        if (!$student instanceof Users || !in_array('ROLE_STUDENT', $student->getRoles(), true)) {
            throw new AccessDeniedException('Acces reserve aux apprentis.');
        }

        $rawSubject = $request->query->get('subject', '');
        $subjectId = is_numeric($rawSubject) && (int) $rawSubject > 0 ? (int) $rawSubject : null;

        $history = $gradesRepository->findHistoryForStudent(student: $student, subjectId: $subjectId);

        return $this->render('student/history/index.html.twig', [
            'cards' => $presenter->prepare($history),
            'subjectOptions' => $gradesRepository->findEvaluatedSubjectsForStudent($student),
            'filters' => ['subject' => $subjectId],
            'stats' => [
                'count' => count($history),
                'globalAverage' => $studentSkillGradeResolver->resolveGlobalAverage($student),
            ],
        ]);
    }
}
