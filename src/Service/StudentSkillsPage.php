<?php

namespace App\Service;

use App\Entity\SkillsUnit;

final readonly class StudentSkillsPage
{
    /**
     * @param list<SkillsUnit> $units
     * @param array<int, array{
     * average: float|null,
     * subjects: list<array{name: string, average: float, coefficient: float, grades: list<array{id: int, value: float}>}>
     * }> $skillGrades
     * @param list<string> $sectionNames
     * @param list<string> $trainingNames
     * @param array{name: string, date: string}|null $latestEvaluatedSubject
     * @param array<int, float|null> $unitAverages
     */
    public function __construct(
        public array $units,
        public array $skillGrades,
        public array $sectionNames,
        public array $trainingNames,
        public ?float $globalAverage,
        public ?array $latestEvaluatedSubject,
        public array $unitAverages = [],
    ) {
    }
}