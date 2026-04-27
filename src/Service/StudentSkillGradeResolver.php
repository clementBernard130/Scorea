<?php

namespace App\Service;

use App\Entity\Users;

final class StudentSkillGradeResolver
{
    /**
     * @return array<int, array{
     * average: float|null,
     * subjects: list<array{name: string, average: float, grades: list<array{id: int, value: float}>}>
     * }>
     */
    public function resolve(Users $student): array
    {
        $skillGrades = [];

        foreach ($student->getGrades() as $grade) {
            $subject = $grade->getTest()?->getSubject();
            $gradeValue = $grade->getGrade();
            $gradeId = $grade->getId(); // On récupère l'ID

            if ($subject === null || $gradeValue === null || $gradeId === null) {
                continue;
            }

            foreach ($subject->getSkills() as $skill) {
                $skillId = $skill->getId();
                $subjectId = $subject->getId();

                if ($skillId === null || $subjectId === null) {
                    continue;
                }

                $skillGrades[$skillId]['subjects'][$subjectId]['name'] = $subject->getName() ?? 'Matière inconnue';
                
                // On stocke maintenant un tableau avec l'id et la valeur
                $skillGrades[$skillId]['subjects'][$subjectId]['grades'][] = [
                    'id' => $gradeId,
                    'value' => $gradeValue,
                ];
            }
        }

        foreach ($skillGrades as $skillId => $skillGradeData) {
            $subjects = [];
            $subjectAverages = [];

            foreach ($skillGradeData['subjects'] ?? [] as $subjectData) {
                $grades = $subjectData['grades'] ?? [];

                if ($grades === []) {
                    continue;
                }

                // On extrait uniquement les valeurs pour calculer la moyenne
                $gradeValues = array_column($grades, 'value');
                $subjectAverage = round(array_sum($gradeValues) / count($gradeValues), 1);
                
                $subjectAverages[] = $subjectAverage;
                $subjects[] = [
                    'name' => $subjectData['name'] ?? 'Matière inconnue',
                    'average' => $subjectAverage,
                    'grades' => $grades,
                ];
            }

            usort(
                $subjects,
                static fn(array $left, array $right): int => strcmp($left['name'], $right['name'])
            );

            $skillGrades[$skillId] = [
                'average' => $subjectAverages === []
                    ? null
                    : round(array_sum($subjectAverages) / count($subjectAverages), 1),
                'subjects' => $subjects,
            ];
        }

        return $skillGrades;
    }

    public function resolveGlobalAverage(Users $student): ?float
    {
        $weightedSum = 0.0;
        $coefficientSum = 0.0;

        foreach ($student->getGrades() as $grade) {
            $gradeValue = $grade->getGrade();
            $coefficient = $grade->getTest()?->getSubject()?->getCoefficient();

            if ($gradeValue === null || $coefficient === null || $coefficient <= 0) {
                continue;
            }

            $weightedSum += $gradeValue * $coefficient;
            $coefficientSum += $coefficient;
        }

        if ($coefficientSum <= 0) {
            return null;
        }

        return round($weightedSum / $coefficientSum, 2);
    }

    /**
     * @return array{name: string, date: string}|null
     */
    public function resolveLatestEvaluatedSubject(Users $student): ?array
    {
        $latestSubject = null;
        $latestDate = null;

        foreach ($student->getGrades() as $grade) {
            $test = $grade->getTest();
            $subject = $test?->getSubject();
            $testDate = $test?->getTestDate();

            if ($subject === null || $testDate === null) {
                continue;
            }

            if ($latestDate === null || $testDate > $latestDate) {
                $latestDate = $testDate;
                $latestSubject = [
                    'name' => $subject->getName() ?? 'Matière inconnue',
                    'date' => $testDate->format('d/m/Y'),
                ];
            }
        }

        return $latestSubject;
    }
}
