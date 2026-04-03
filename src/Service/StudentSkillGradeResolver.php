<?php

namespace App\Service;

use App\Entity\Users;

final class StudentSkillGradeResolver
{
    /**
     * @return array<int, array{
     *     average: float|null,
     *     subjects: list<array{name: string, average: float, grades: list<float>}>
     * }>
     */
    public function resolve(Users $student): array
    {
        $skillGrades = [];

        foreach ($student->getGrades() as $grade) {
            $subject = $grade->getTest()?->getSubject();
            $gradeValue = $grade->getGrade();

            if ($subject === null || $gradeValue === null) {
                continue;
            }

            foreach ($subject->getSkills() as $skill) {
                $skillId = $skill->getId();
                $subjectId = $subject->getId();

                if ($skillId === null || $subjectId === null) {
                    continue;
                }

                $skillGrades[$skillId]['subjects'][$subjectId]['name'] = $subject->getName() ?? 'Matière inconnue';
                $skillGrades[$skillId]['subjects'][$subjectId]['grades'][] = $gradeValue;
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

                $subjectAverage = round(array_sum($grades) / count($grades), 1);
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
}
