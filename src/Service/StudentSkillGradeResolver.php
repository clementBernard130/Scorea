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
                $skillGrades[$skillId]['subjects'][$subjectId]['coefficient'] = $subject->getCoefficient() ?? 1.0;

                // On stocke maintenant un tableau avec l'id et la valeur
                $skillGrades[$skillId]['subjects'][$subjectId]['grades'][] = [
                    'id' => $gradeId,
                    'value' => $gradeValue,
                ];
            }
        }

        foreach ($skillGrades as $skillId => $skillGradeData) {
            $subjects = [];
            $weightedSum = 0.0;
            $coefficientSum = 0.0;

            foreach ($skillGradeData['subjects'] ?? [] as $subjectData) {
                $grades = $subjectData['grades'] ?? [];

                if ($grades === []) {
                    continue;
                }

                // On extrait uniquement les valeurs pour calculer la moyenne
                $gradeValues = array_column($grades, 'value');
                $subjectAverage = round(array_sum($gradeValues) / count($gradeValues), 1);
                $coefficient = $subjectData['coefficient'] ?? 1.0;

                $weightedSum += $subjectAverage * $coefficient;
                $coefficientSum += $coefficient;

                $subjects[] = [
                    'name' => $subjectData['name'] ?? 'Matière inconnue',
                    'average' => $subjectAverage,
                    'coefficient' => $coefficient,
                    'grades' => $grades,
                ];
            }

            usort(
                $subjects,
                static fn(array $left, array $right): int => strcmp($left['name'], $right['name'])
            );

            $skillGrades[$skillId] = [
                'average' => $coefficientSum <= 0
                    ? null
                    : round($weightedSum / $coefficientSum, 1),
                'subjects' => $subjects,
            ];
        }

        return $skillGrades;
    }

    /**
     * @param list<\App\Entity\SkillsUnit> $units
     * @return array<int, float|null>
     */
    public function resolveUnitAverages(Users $student, array $units): array
    {
        $subjectData = [];

        foreach ($student->getGrades() as $grade) {
            $gradeValue = $grade->getGrade();
            $subject = $grade->getTest()?->getSubject();
            $subjectId = $subject?->getId();
            $coefficient = $subject?->getCoefficient();

            if ($gradeValue === null || $subject === null || $subjectId === null || $coefficient === null || $coefficient <= 0) {
                continue;
            }

            if (!isset($subjectData[$subjectId])) {
                $unitIds = [];
                foreach ($subject->getSkills() as $skill) {
                    $unitId = $skill->getSkillUnit()?->getId();
                    if ($unitId !== null) {
                        $unitIds[$unitId] = $unitId;
                    }
                }

                $subjectData[$subjectId] = [
                    'gradeSum' => 0.0,
                    'gradeCount' => 0,
                    'coefficient' => $coefficient,
                    'unitIds' => $unitIds,
                ];
            }

            $subjectData[$subjectId]['gradeSum'] += $gradeValue;
            $subjectData[$subjectId]['gradeCount']++;
        }

        $unitTotals = [];

        foreach ($subjectData as $data) {
            if ($data['gradeCount'] === 0) {
                continue;
            }

            $subjectAverage = $data['gradeSum'] / $data['gradeCount'];
            $coefficient = $data['coefficient'];

            foreach ($data['unitIds'] as $unitId) {
                if (!isset($unitTotals[$unitId])) {
                    $unitTotals[$unitId] = ['weightedSum' => 0.0, 'coeffSum' => 0.0];
                }
                $unitTotals[$unitId]['weightedSum'] += $subjectAverage * $coefficient;
                $unitTotals[$unitId]['coeffSum'] += $coefficient;
            }
        }

        $result = [];
        foreach ($units as $unit) {
            $unitId = $unit->getId();
            if ($unitId === null) {
                continue;
            }
            $totals = $unitTotals[$unitId] ?? null;
            $result[$unitId] = ($totals !== null && $totals['coeffSum'] > 0)
                ? round($totals['weightedSum'] / $totals['coeffSum'], 2)
                : null;
        }

        return $result;
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
