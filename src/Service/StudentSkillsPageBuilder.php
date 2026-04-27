<?php

namespace App\Service;

use App\Entity\Users;
use App\Repository\SkillsUnitRepository;

final class StudentSkillsPageBuilder
{
    public function __construct(
        private readonly SkillsUnitRepository $skillsUnitRepository,
        private readonly StudentSkillGradeResolver $studentSkillGradeResolver,
    ) {
    }

    public function build(Users $student): StudentSkillsPage
    {
        $sectionNames = [];
        $trainingNames = [];
        $trainingIds = [];

        foreach ($student->getSections() as $section) {
            $sectionName = $section->getName();
            $training = $section->getTraining();
            $trainingId = $training?->getId();
            $trainingName = $training?->getName();

            if ($sectionName !== null) {
                $sectionNames[$sectionName] = $sectionName;
            }

            if ($trainingId !== null) {
                $trainingIds[$trainingId] = $trainingId;
            }

            if ($trainingName !== null) {
                $trainingNames[$trainingName] = $trainingName;
            }
        }

        return new StudentSkillsPage(
            units: $this->skillsUnitRepository->findByTrainingIds(array_values($trainingIds)),
            skillGrades: $this->studentSkillGradeResolver->resolve($student),
            sectionNames: array_values($sectionNames),
            trainingNames: array_values($trainingNames),
            globalAverage: $this->studentSkillGradeResolver->resolveGlobalAverage($student),
            latestEvaluatedSubject: $this->studentSkillGradeResolver->resolveLatestEvaluatedSubject($student),
        );
    }
}
