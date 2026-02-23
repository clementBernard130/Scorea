<?php

namespace App\Entity;

use App\Repository\GradesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradesRepository::class)]
class Grades
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $grade = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Subject $subject = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $student = null;

    #[ORM\ManyToOne(inversedBy: 'givenGrades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $teacher = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?float
    {
        return $this->grade;
    }

    public function setGrade(float $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getStudent(): ?Users
    {
        return $this->student;
    }

    public function setStudent(?Users $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getTeacher(): ?Users
    {
        return $this->teacher;
    }

    public function setTeacher(?Users $teacher): static
    {
        $this->teacher = $teacher;

        return $this;
    }
}
