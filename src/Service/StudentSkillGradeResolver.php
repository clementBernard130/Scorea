<?php

namespace App\Service;

use App\Entity\Skills;
use App\Entity\Users;

final class StudentSkillGradeResolver
{

    /**
     * Construit la table de pondération gradeTypeNameId → pourcentage (0-100) pour une compétence.
     * La somme des pourcentages doit valoir 100 (ex : DS=100 ou Oral Projet=60 + CC=40).
     *
     * @return array<int, int>
     */
    private function buildTypeWeightMap(Skills $skill): array
    {
        $map = [];
        foreach ($skill->getGradeTypes() as $gradeType) {
            $typeId = $gradeType->getType()?->getId();
            if ($typeId !== null) {
                $map[$typeId] = $gradeType->getWeight();
            }
        }

        return $map;
    }

    /**

     *
     * @param array<int|string, list<float>> $gradesByType  typeId (ou 'none') → valeurs
     * @param array<int, int>                $typeWeights   typeId → pourcentage (0-100)
     */
    private function computeTypeWeightedAverage(array $gradesByType, array $typeWeights): float
    {
        $typeWeightedSum = 0.0;
        $typeWeightSum   = 0.0;
        $allValues       = [];

        foreach ($gradesByType as $typeKey => $values) {
            $typeAvg   = array_sum($values) / count($values);
            array_push($allValues, ...$values);
            $weight = is_int($typeKey) ? ($typeWeights[$typeKey] ?? null) : null;
            if ($weight !== null && $weight > 0) {
                $typeWeightedSum += $typeAvg * $weight;
                $typeWeightSum   += $weight;
            }
        }

        if ($typeWeightSum > 0) {
            $totalConfiguredWeight = array_sum($typeWeights) ?: $typeWeightSum;
            return $typeWeightedSum / $totalConfiguredWeight;
        }

        // Repli sur la moyenne arithmétique si aucune pondération définie
        return count($allValues) > 0 ? array_sum($allValues) / count($allValues) : 0.0;
    }

    /**
     * @return array<int, array{
     *   average: float|null,
     *   subjects: list<array{name: string, average: float, coefficient: float, grades: list<array{id: int, value: float}>}>
     * }>
     */
    public function resolve(Users $student): array
    {
        $skillData = [];

        foreach ($student->getGrades() as $grade) {
            $test        = $grade->getTest();
            $subject     = $test?->getSubject();
            $gradeValue  = $grade->getGrade();
            $gradeId     = $grade->getId();
            $gradeTypeId = $test?->getGradeType()?->getId();

            if ($subject === null || $gradeValue === null || $gradeId === null) {
                continue;
            }

            foreach ($subject->getSkills() as $skill) {
                $skillId   = $skill->getId();
                $subjectId = $subject->getId();

                if ($skillId === null || $subjectId === null) {
                    continue;
                }

                // Initialise la table de pondération par type d'éval pour cette compétence
                if (!isset($skillData[$skillId])) {
                    $skillData[$skillId] = [
                        'typeWeights' => $this->buildTypeWeightMap($skill),
                        'subjects'    => [],
                    ];
                }

                $skillData[$skillId]['subjects'][$subjectId]['name']        = $subject->getName() ?? 'Matière inconnue';
                $skillData[$skillId]['subjects'][$subjectId]['coefficient'] = $subject->getCoefficient() ?? 1.0;
                $skillData[$skillId]['subjects'][$subjectId]['grades'][]    = [
                    'id'         => $gradeId,
                    'value'      => $gradeValue,
                    'gradeTypeId' => $gradeTypeId,
                ];
            }
        }

        $result = [];

        foreach ($skillData as $skillId => $skill) {
            $typeWeights    = $skill['typeWeights'];
            $subjects       = [];
            $weightedSum    = 0.0;
            $coefficientSum = 0.0;

            foreach ($skill['subjects'] as $subjectData) {
                $grades = $subjectData['grades'] ?? [];
                if ($grades === []) {
                    continue;
                }

                // Groupe les notes par type d'éval
                $gradesByType = [];
                foreach ($grades as $g) {
                    $key = $g['gradeTypeId'] ?? 'none';
                    $gradesByType[$key][] = $g['value'];
                }

                $subjectAverage = round($this->computeTypeWeightedAverage($gradesByType, $typeWeights), 1);
                $coefficient    = $subjectData['coefficient'];

                $weightedSum    += $subjectAverage * $coefficient;
                $coefficientSum += $coefficient;

                $subjects[] = [
                    'name'        => $subjectData['name'],
                    'average'     => $subjectAverage,
                    'coefficient' => $coefficient,
                    'grades'      => array_map(
                        static fn(array $g): array => ['id' => $g['id'], 'value' => $g['value']],
                        $grades
                    ),
                ];
            }

            usort(
                $subjects,
                static fn(array $left, array $right): int => strcmp($left['name'], $right['name'])
            );

            $result[$skillId] = [
                'average'  => $coefficientSum > 0
                    ? round($weightedSum / $coefficientSum, 1)
                    : null,
                'subjects' => $subjects,
            ];

        }

        return $result;
    }

    /**
     * Moyenne par unité = moyenne arithmétique des moyennes de compétences (les compétences
     * n'ayant pas de coefficient propre, elles ont toutes le même poids au sein d'une unité).
     * Chaque moyenne de compétence intègre la pondération par type d'éval et par matière.
     *
     * @param list<\App\Entity\SkillsUnit> $units
     * @return array<int, float|null>
     */
    public function resolveUnitAverages(Users $student, array $units): array
    {
        $skillGrades = $this->resolve($student);

        $result = [];
        foreach ($units as $unit) {
            $unitId = $unit->getId();
            if ($unitId === null) {
                continue;
            }

            $skillAverages = [];
            foreach ($unit->getSkills() as $skill) {
                $skillId = $skill->getId();
                if ($skillId === null) {
                    continue;
                }
                $avg = $skillGrades[$skillId]['average'] ?? null;
                if ($avg !== null) {
                    $skillAverages[] = $avg;
                }
            }

            $result[$unitId] = $skillAverages !== []
                ? round(array_sum($skillAverages) / count($skillAverages), 2)
                : null;
        }

        return $result;
    }

    /**
     * Moyenne globale = moyenne des moyennes de matières pondérée par coefficient.
     * Chaque moyenne de matière = moyenne arithmétique des moyennes de compétences liées,
     * chaque moyenne de compétence étant pondérée par type d'éval.
     */
    public function resolveGlobalAverage(Users $student): ?float
    {
        // 1. Collecte des notes par matière → compétence → type d'éval
        $subjectData = [];

        foreach ($student->getGrades() as $grade) {
            $gradeValue  = $grade->getGrade();
            $test        = $grade->getTest();
            $subject     = $test?->getSubject();
            $subjectId   = $subject?->getId();
            $coefficient = $subject?->getCoefficient();
            $gradeTypeId = $test?->getGradeType()?->getId();

            if ($gradeValue === null || $subject === null || $subjectId === null || $coefficient === null || $coefficient <= 0) {
                continue;
            }

            if (!isset($subjectData[$subjectId])) {
                $subjectData[$subjectId] = [
                    'coefficient' => $coefficient,
                    'skills'      => [],
                ];

                foreach ($subject->getSkills() as $skill) {
                    $skillId = $skill->getId();
                    if ($skillId === null) {
                        continue;
                    }
                    $subjectData[$subjectId]['skills'][$skillId] = [
                        'typeWeights'  => $this->buildTypeWeightMap($skill),
                        'gradesByType' => [],
                    ];
                }
            }

            $key = $gradeTypeId ?? 'none';

            // Distribue la note dans chaque compétence liée à la matière
            foreach ($subjectData[$subjectId]['skills'] as &$skillEntry) {
                $skillEntry['gradesByType'][$key][] = $gradeValue;
            }
            unset($skillEntry);
        }

        // 2. Calcul de la moyenne globale
        $weightedSum    = 0.0;
        $coefficientSum = 0.0;

        foreach ($subjectData as $data) {
            $skillAverages = [];

            foreach ($data['skills'] as $skillEntry) {
                if ($skillEntry['gradesByType'] === []) {
                    continue;
                }
                $skillAverages[] = $this->computeTypeWeightedAverage(
                    $skillEntry['gradesByType'],
                    $skillEntry['typeWeights']
                );
            }

            if ($skillAverages === []) {
                continue;
            }

            // Moyenne de la matière = moyenne arithmétique des moyennes de compétences
            $subjectAvg      = array_sum($skillAverages) / count($skillAverages);
            $weightedSum    += $subjectAvg * $data['coefficient'];
            $coefficientSum += $data['coefficient'];
        }

        return $coefficientSum > 0 ? round($weightedSum / $coefficientSum, 2) : null;
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
