<?php

namespace App\Entity;

use App\Repository\TrainingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingsRepository::class)]
#[ORM\Table(name: 'trainings')]
class Trainings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Sections>
     */
    #[ORM\OneToMany(targetEntity: Sections::class, mappedBy: 'training', orphanRemoval: true)]
    private Collection $sections;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, SkillsUnit>
     */
    #[ORM\OneToMany(targetEntity: SkillsUnit::class, mappedBy: 'trainings')]
    private Collection $skillUnits;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->skillUnits = new ArrayCollection();
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

    /**
     * @return Collection<int, Sections>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Sections $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setTraining($this);
        }

        return $this;
    }

    public function removeSection(Sections $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getTraining() === $this) {
                $section->setTraining(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return Collection<int, SkillsUnit>
     */
    public function getSkillUnits(): Collection
    {
        return $this->skillUnits;
    }

    public function addSkillUnit(SkillsUnit $skillUnit): static
    {
        if (!$this->skillUnits->contains($skillUnit)) {
            $this->skillUnits->add($skillUnit);
            $skillUnit->setTrainings($this);
        }

        return $this;
    }

    public function removeSkillUnit(SkillsUnit $skillUnit): static
    {
        if ($this->skillUnits->removeElement($skillUnit)) {
            // set the owning side to null (unless already changed)
            if ($skillUnit->getTrainings() === $this) {
                $skillUnit->setTrainings(null);
            }
        }

        return $this;
    }
}
