<?php

namespace App\Service;

use App\Entity\Grades;

final class EvaluationHistoryPresenter
{
    /**
     * @param list<Grades> $history
     * @return list<array{
     *   grade: Grades,
     *   dateLabel: string,
     *   subjectName: string,
     *   skills: list<string>,
     *   gradeTypeLabel: string|null,
     *   formattedGrade: string,
     *   progressValue: int,
     *   trendLabel: string,
     *   trendArrow: string,
     *   trendClass: string,
     *   trendDelta: float|null,
     *   comment: string,
     * }>
     */
    public function prepare(array $history): array
    {
        $trendByGradeId = $this->buildTrendByGradeId($history);
        $cards = [];

        foreach ($history as $grade) {
            $gradeId = $grade->getId();
            $test = $grade->getTest();
            $subject = $test?->getSubject();

            $trend = ($gradeId !== null && isset($trendByGradeId[$gradeId]))
                ? $trendByGradeId[$gradeId]
                : ['direction' => 'na', 'delta' => null];

            $badge = $this->resolveTrendBadge($trend['direction']);

            $skillNames = [];
            if ($subject !== null) {
                foreach ($subject->getSkills()->slice(0, 2) as $skill) {
                    $skillNames[] = $skill->getName();
                }
            }

            $cards[] = [
                'grade' => $grade,
                'dateLabel' => $this->resolveRelativeDateLabel($test?->getTestDate()),
                'subjectName' => $subject?->getName() ?? 'Evaluation',
                'skills' => $skillNames,
                'gradeTypeLabel' => $grade->getGradeType()?->getName(),
                'formattedGrade' => $grade->getGrade() !== null
                    ? number_format($grade->getGrade(), 2, ',', '') . '/20'
                    : '-',
                'progressValue' => $grade->getGrade() !== null
                    ? (int) floor(($grade->getGrade() / 20) * 100)
                    : 0,
                'trendLabel' => $badge['label'],
                'trendArrow' => $badge['arrow'],
                'trendClass' => $badge['class'],
                'trendDelta' => $trend['delta'],
                'comment' => $grade->getComment() ?? 'Aucun commentaire pour cette evaluation.',
            ];
        }

        return $cards;
    }

    /**
     * @param list<Grades> $history
     * @return array<int, array{direction: string, delta: float|null}>
     */
    private function buildTrendByGradeId(array $history): array
    {
        $gradesBySubject = [];

        foreach ($history as $grade) {
            $gradeId = $grade->getId();
            $subjectId = $grade->getTest()?->getSubject()?->getId();

            if ($gradeId === null || $subjectId === null) {
                continue;
            }

            $gradesBySubject[$subjectId][] = $grade;
        }

        $trendByGradeId = [];

        foreach ($gradesBySubject as $grades) {
            $count = count($grades);
            for ($index = 0; $index < $count; $index++) {
                $current = $grades[$index];
                $currentId = $current->getId();
                $currentValue = $current->getGrade();

                if ($currentId === null || $currentValue === null) {
                    continue;
                }

                $previous = $grades[$index + 1] ?? null;
                $previousValue = $previous?->getGrade();

                if ($previousValue === null) {
                    $trendByGradeId[$currentId] = ['direction' => 'na', 'delta' => null];
                    continue;
                }

                $delta = round($currentValue - $previousValue, 2);
                $trendByGradeId[$currentId] = [
                    'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
                    'delta' => $delta,
                ];
            }
        }

        return $trendByGradeId;
    }

    /**
     * @return array{label: string, arrow: string, class: string}
     */
    private function resolveTrendBadge(string $direction): array
    {
        return match ($direction) {
            'up' => [
                'label' => 'Progression',
                'arrow' => '↗',
                'class' => 'text-emerald-700 bg-emerald-100 border-emerald-200',
            ],
            'down' => [
                'label' => 'En baisse',
                'arrow' => '↘',
                'class' => 'text-rose-700 bg-rose-100 border-rose-200',
            ],
            'flat' => [
                'label' => 'Stable',
                'arrow' => '→',
                'class' => 'text-slate-700 bg-slate-100 border-slate-200',
            ],
            default => [
                'label' => 'Premiere reference',
                'arrow' => '•',
                'class' => 'text-slate-700 bg-slate-100 border-slate-200',
            ],
        };
    }

    private function resolveRelativeDateLabel(?\DateTimeInterface $date): string
    {
        if ($date === null) {
            return 'Date inconnue';
        }

        $today = new \DateTimeImmutable('today');
        $dateOnly = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
        $diff = (int) $today->diff($dateOnly)->days;

        return match (true) {
            $diff === 0 => "Aujourd'hui",
            $diff === 1 => 'Hier',
            $diff <= 30 => "Il y a {$diff} jours",
            default => $date->format('d/m/Y'),
        };
    }
}
