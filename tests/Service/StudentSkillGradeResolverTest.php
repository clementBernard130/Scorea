<?php

namespace App\Tests\Service;

use App\Entity\GradeTypeNames;
use App\Entity\GradeTypes;
use App\Entity\Grades;
use App\Entity\Skills;
use App\Entity\SkillsUnit;
use App\Entity\Subjects;
use App\Entity\Tests;
use App\Entity\Users;
use App\Service\StudentSkillGradeResolver;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Teste le calcul des moyennes avec pondération en pourcentage par type d'éval.
 *
 * Règle métier :
 *   - Bloc 1 & 2 : un seul type d'éval,   poids = 100 %
 *   - Bloc 3     : deux types d'éval,      poids = 60 % (Oral Projet) + 40 % (Contrôle Continu)
 *   - Validation d'une compétence          : moyenne ≥ 10/20
 *
 * Modèle de données (setUp) :
 *   Types d'éval : "Oral Projet" (id=1, 60 %), "Contrôle Continu" (id=2, 40 %), "DS" (id=3, 100 %)
 *
 *   C1 (id=10, Bloc 3)   → Oral Projet 60 % + Contrôle Continu 40 %
 *   C2 (id=20, Bloc 1/2) → DS 100 %
 *
 *   M1 (id=100, coeff=2) liée à C1
 *   M2 (id=200, coeff=1) liée à C2
 *   Unité U1 (id=1) → C1, C2
 *
 * Notes de l'étudiant (setUp) :
 *   T1 (M1, Oral Projet)      → 12 ┐ avg Oral = 14
 *   T2 (M1, Oral Projet)      → 16 ┘
 *   T3 (M1, Contrôle Continu) → 10   avg CC   = 10
 *   T4 (M2, DS)               →  8 ┐ avg DS   = 10
 *   T5 (M2, DS)               → 12 ┘
 *
 * Calculs attendus :
 *   C1 × M1  = (14×60 + 10×40) / 100 = 12,4
 *   C2 × M2  = (10×100) / 100        = 10,0
 *   C1 avg   = 12,4  (seule matière M1, coeff 2 → pas d'impact)
 *   C2 avg   = 10,0  (seule matière M2, coeff 1 → pas d'impact)
 *   U1 avg   = (12,4 + 10,0) / 2     = 11,2
 *   Global   = (12,4×2 + 10,0×1) / 3 ≈ 11,6
 */
final class StudentSkillGradeResolverTest extends TestCase
{
    private StudentSkillGradeResolver $resolver;

    private GradeTypeNames $typeOral;
    private GradeTypeNames $typeCC;
    private GradeTypeNames $typeDS;

    private Skills    $skillC1;     // Bloc 3  : Oral 60 % + CC 40 %
    private Skills    $skillC2;     // Bloc 1/2 : DS 100 %
    private Subjects  $subjectM1;   // coeff 2, lié à C1
    private Subjects  $subjectM2;   // coeff 1, lié à C2
    private SkillsUnit $unitU1;
    private Users     $student;

    protected function setUp(): void
    {
        $this->resolver = new StudentSkillGradeResolver();

        // Types d'éval
        $this->typeOral = $this->makeGradeTypeName(1, 'Oral Projet');
        $this->typeCC   = $this->makeGradeTypeName(2, 'Contrôle Continu');
        $this->typeDS   = $this->makeGradeTypeName(3, 'DS');

        // C1 (Bloc 3) : Oral 60 % + CC 40 %
        $gtC1Oral = $this->makeGradeType($this->typeOral, 60);
        $gtC1CC   = $this->makeGradeType($this->typeCC,   40);
        $this->skillC1 = $this->makeSkill(10, 'C1 – Bloc 3', [$gtC1Oral, $gtC1CC]);
        $gtC1Oral->setSkill($this->skillC1);
        $gtC1CC->setSkill($this->skillC1);

        // C2 (Bloc 1/2) : DS 100 %
        $gtC2DS = $this->makeGradeType($this->typeDS, 100);
        $this->skillC2 = $this->makeSkill(20, 'C2 – Bloc 1', [$gtC2DS]);
        $gtC2DS->setSkill($this->skillC2);

        // Matières
        $this->subjectM1 = $this->makeSubject(100, 'M1', 2.0, [$this->skillC1]);
        $this->subjectM2 = $this->makeSubject(200, 'M2', 1.0, [$this->skillC2]);

        // Unité
        $this->unitU1 = $this->makeUnit(1, 'U1', [$this->skillC1, $this->skillC2]);

        // Notes
        $t1 = $this->makeTest($this->subjectM1, $this->typeOral);
        $t2 = $this->makeTest($this->subjectM1, $this->typeOral);
        $t3 = $this->makeTest($this->subjectM1, $this->typeCC);
        $t4 = $this->makeTest($this->subjectM2, $this->typeDS);
        $t5 = $this->makeTest($this->subjectM2, $this->typeDS);

        $this->student = $this->makeStudent([
            $this->makeGrade(1, $t1, 12.0),
            $this->makeGrade(2, $t2, 16.0),
            $this->makeGrade(3, $t3, 10.0),
            $this->makeGrade(4, $t4,  8.0),
            $this->makeGrade(5, $t5, 12.0),
        ]);
    }

    // =========================================================================
    // Pondération par type d'éval (pourcentages)
    // =========================================================================

    public function testBloc3SplitWeight60And40ComputesCorrectAverage(): void
    {
        $result = $this->resolver->resolve($this->student);

        // C1 × M1 : avg Oral=14 (60 %) + avg CC=10 (40 %)
        // = (14×60 + 10×40) / 100 = 1240/100 = 12,4
        $this->assertArrayHasKey(10, $result);
        $this->assertEqualsWithDelta(12.4, $result[10]['subjects'][0]['average'], 0.05);
    }

    public function testBloc12SingleWeight100GivesArithmeticAverage(): void
    {
        $result = $this->resolver->resolve($this->student);

        // C2 × M2 : avg DS = (8+12)/2 = 10, poids 100 %
        // = (10×100) / 100 = 10,0
        $this->assertArrayHasKey(20, $result);
        $this->assertEqualsWithDelta(10.0, $result[20]['subjects'][0]['average'], 0.01);
    }

    public function testWhenOnlyOneEvalTypeIsPresent_NormalizesToAvailablePercentage(): void
    {
        // Seul Oral Projet (60 %) a été évalué, CC (40 %) absent encore
        // → normalise sur 60 : (14×60)/60 = 14
        $gtOral  = $this->makeGradeType($this->typeOral, 60);
        $gtCC    = $this->makeGradeType($this->typeCC,   40);
        $skill   = $this->makeSkill(10, 'C1', [$gtOral, $gtCC]);
        $gtOral->setSkill($skill);
        $gtCC->setSkill($skill);

        $subject = $this->makeSubject(100, 'M1', 1.0, [$skill]);
        $t1      = $this->makeTest($subject, $this->typeOral);
        $t2      = $this->makeTest($subject, $this->typeOral);

        $student = $this->makeStudent([
            $this->makeGrade(1, $t1, 12.0),
            $this->makeGrade(2, $t2, 16.0),
        ]);

        $result = $this->resolver->resolve($student);

        $this->assertEqualsWithDelta(14.0, $result[10]['subjects'][0]['average'], 0.01);
    }

    // =========================================================================
    // Pondération par coefficient de matière
    // =========================================================================

    public function testSkillAverageWeightedBySubjectCoefficient(): void
    {
        // Compétence C3 liée à Ma (coeff 3) et Mb (coeff 1), DS=100 % sur les deux
        $typeDS  = $this->makeGradeTypeName(3, 'DS');
        $gtA     = $this->makeGradeType($typeDS, 100);
        $gtB     = $this->makeGradeType($typeDS, 100);
        $skill   = $this->makeSkill(30, 'C3', [$gtA, $gtB]);
        $gtA->setSkill($skill);
        $gtB->setSkill($skill);

        $subjectA = $this->makeSubject(300, 'Ma', 3.0, [$skill]);
        $subjectB = $this->makeSubject(400, 'Mb', 1.0, [$skill]);

        $student = $this->makeStudent([
            $this->makeGrade(1, $this->makeTest($subjectA, $typeDS), 15.0), // Ma avg=15
            $this->makeGrade(2, $this->makeTest($subjectB, $typeDS),  7.0), // Mb avg= 7
        ]);

        $result = $this->resolver->resolve($student);

        // (15×3 + 7×1) / (3+1) = 52/4 = 13,0
        $this->assertEqualsWithDelta(13.0, $result[30]['average'], 0.01);
    }

    // =========================================================================
    // Validation des compétences (seuil ≥ 10/20)
    // =========================================================================

    public function testSkillAbove10IsValidated(): void
    {
        $result = $this->resolver->resolve($this->student);

        // C1 avg = 12,4 ≥ 10 → validée
        $this->assertGreaterThanOrEqual(10.0, $result[10]['average']);
    }

    public function testSkillAtExactly10IsValidated(): void
    {
        $result = $this->resolver->resolve($this->student);

        // C2 avg = 10,0 → exactement au seuil, validée
        $this->assertGreaterThanOrEqual(10.0, $result[20]['average']);
    }

    public function testSkillBelow10IsNotValidated(): void
    {
        // Oral=6, CC=7 → C1 avg = (6×60 + 7×40)/100 = (360+280)/100 = 6,4 < 10
        $gtOral  = $this->makeGradeType($this->typeOral, 60);
        $gtCC    = $this->makeGradeType($this->typeCC,   40);
        $skill   = $this->makeSkill(10, 'C1', [$gtOral, $gtCC]);
        $gtOral->setSkill($skill);
        $gtCC->setSkill($skill);

        $subject = $this->makeSubject(100, 'M1', 1.0, [$skill]);

        $student = $this->makeStudent([
            $this->makeGrade(1, $this->makeTest($subject, $this->typeOral), 6.0),
            $this->makeGrade(2, $this->makeTest($subject, $this->typeCC),   7.0),
        ]);

        $result = $this->resolver->resolve($student);

        $this->assertLessThan(10.0, $result[10]['average']);
        $this->assertEqualsWithDelta(6.4, $result[10]['average'], 0.05);
    }

    // =========================================================================
    // resolveUnitAverages()
    // =========================================================================

    public function testUnitAverageIsArithmeticMeanOfSkillAverages(): void
    {
        $result = $this->resolver->resolveUnitAverages($this->student, [$this->unitU1]);

        // C1 avg=12,4 ; C2 avg=10,0 → U1 = (12,4 + 10,0) / 2 = 11,2
        $this->assertArrayHasKey(1, $result);
        $this->assertEqualsWithDelta(11.2, $result[1], 0.05);
    }

    public function testUnitAverageReturnsNullForUnitWithNoGrades(): void
    {
        $result = $this->resolver->resolveUnitAverages($this->student, [$this->makeUnit(99, 'Vide', [])]);

        $this->assertNull($result[99]);
    }

    // =========================================================================
    // resolveGlobalAverage()
    // =========================================================================

    public function testGlobalAverageWeightedBySubjectCoefficientAndEvalTypePercentages(): void
    {
        $avg = $this->resolver->resolveGlobalAverage($this->student);

        // M1 (coeff 2) : (14×60 + 10×40)/100 = 12,4
        // M2 (coeff 1) : (10×100)/100         = 10,0
        // Global = (12,4×2 + 10,0×1) / 3      = 34,8/3 ≈ 11,6
        $this->assertNotNull($avg);
        $this->assertEqualsWithDelta(11.6, $avg, 0.05);
    }

    public function testGlobalAverageReturnsNullWhenNoGrades(): void
    {
        $this->assertNull($this->resolver->resolveGlobalAverage($this->makeStudent([])));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makeGradeTypeName(int $id, string $name): GradeTypeNames
    {
        $type = new GradeTypeNames();
        $this->setId($type, $id);
        $type->setName($name);

        return $type;
    }

    private function makeGradeType(GradeTypeNames $typeName, int $percentageWeight): GradeTypes
    {
        $gt = new GradeTypes();
        $gt->setType($typeName);
        $gt->setWeight($percentageWeight);

        return $gt;
    }

    private function makeSkill(int $id, string $name, array $gradeTypes): Skills
    {
        $skill = new Skills();
        $this->setId($skill, $id);
        $skill->setName($name);
        $skill->setDescription('');
        $this->setProperty($skill, 'gradeTypes', new ArrayCollection($gradeTypes));

        return $skill;
    }

    private function makeSubject(int $id, string $name, float $coeff, array $skills): Subjects
    {
        $subject = new Subjects();
        $this->setId($subject, $id);
        $subject->setName($name);
        $subject->setDescription('');
        $subject->setCoefficient($coeff);
        $this->setProperty($subject, 'skills', new ArrayCollection($skills));

        return $subject;
    }

    private function makeUnit(int $id, string $name, array $skills): SkillsUnit
    {
        $unit = new SkillsUnit();
        $this->setId($unit, $id);
        $unit->setName($name);
        $this->setProperty($unit, 'skills', new ArrayCollection($skills));

        return $unit;
    }

    private function makeTest(Subjects $subject, GradeTypeNames $gradeType): Tests
    {
        $test = new Tests();
        $test->setSubject($subject);
        $test->setTestDate(new \DateTime());
        $this->setProperty($test, 'gradeType', $gradeType);

        return $test;
    }

    private function makeGrade(int $id, Tests $test, float $value): Grades
    {
        $grade = new Grades();
        $this->setId($grade, $id);
        $grade->setTest($test);
        $grade->setGrade($value);
        $grade->setCreatedAt(new \DateTimeImmutable());
        $grade->setUpdatedAt(new \DateTimeImmutable());

        return $grade;
    }

    /** @param Grades[] $grades */
    private function makeStudent(array $grades): Users
    {
        $student = new Users();
        $this->setProperty($student, 'grades', new ArrayCollection($grades));

        return $student;
    }

    private function setId(object $entity, int $id): void
    {
        $ref = new \ReflectionProperty($entity, 'id');
        $ref->setAccessible(true);
        $ref->setValue($entity, $id);
    }

    private function setProperty(object $entity, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty($entity, $property);
        $ref->setAccessible(true);
        $ref->setValue($entity, $value);
    }
}