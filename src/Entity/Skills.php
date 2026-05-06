<?php

namespace App\Entity;

use App\Repository\SkillsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillsRepository::class)]
#[ORM\Table(name: 'skills')]
class Skills
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: true)]
    private ?SkillsUnit $skillUnit = null;

    /**
     * @var Collection<int, Subjects>
     */
    #[ORM\ManyToMany(targetEntity: Subjects::class, mappedBy: 'skills', cascade: ['persist'])]
    private Collection $subjects;

    /**
     * @var Collection<int, GradeTypes>
     */
    #[ORM\OneToMany(targetEntity: GradeTypes::class, mappedBy: 'skill', orphanRemoval: true, cascade: ['persist'])]
    private Collection $gradeTypes;

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
        $this->gradeTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSkillUnit(): ?SkillsUnit
    {
        return $this->skillUnit;
    }

    public function setSkillUnit(?SkillsUnit $skillUnit): static
    {
        $this->skillUnit = $skillUnit;

        return $this;
    }

    /**
     * @return Collection<int, Subjects>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subjects $subject): static
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->addSkill($this);
        }

        return $this;
    }

    public function removeSubject(Subjects $subject): static
    {
        if ($this->subjects->removeElement($subject)) {
            $subject->removeSkill($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, GradeTypes>
     */
    public function getGradeTypes(): Collection
    {
        return $this->gradeTypes;
    }

    public function addGradeType(GradeTypes $gradeType): static
    {
        if (!$this->gradeTypes->contains($gradeType)) {
            $this->gradeTypes->add($gradeType);
            $gradeType->setSkill($this);
        }

        return $this;
    }

    public function removeGradeType(GradeTypes $gradeType): static
    {
        if ($this->gradeTypes->removeElement($gradeType)) {
            // set the owning side to null (unless already changed)
            if ($gradeType->getSkill() === $this) {
                $gradeType->setSkill(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
