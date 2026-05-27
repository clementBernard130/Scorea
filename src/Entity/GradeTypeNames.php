<?php

namespace App\Entity;

use App\Repository\GradeTypeNamesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeTypeNamesRepository::class)]
class GradeTypeNames
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;


    /**
     * @var Collection<int, GradeTypes>
     */
    #[ORM\OneToMany(targetEntity: GradeTypes::class, mappedBy: 'type', orphanRemoval: true)]
    private Collection $gradeTypes;

    /**
     * @var Collection<int, Tests>
     */
    #[ORM\OneToMany(targetEntity: Tests::class, mappedBy: 'gradeType')]
    private Collection $tests;

    public function __construct()
    {
        $this->gradeTypes = new ArrayCollection();
        $this->tests = new ArrayCollection();
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
            $gradeType->setType($this);
        }

        return $this;
    }

    public function removeGradeType(GradeTypes $gradeType): static
    {
        if ($this->gradeTypes->removeElement($gradeType)) {
            // set the owning side to null (unless already changed)
            if ($gradeType->getType() === $this) {
                $gradeType->setType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tests>
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Tests $test): static
    {
        if (!$this->tests->contains($test)) {
            $this->tests->add($test);
            $test->setGradeType($this);
        }

        return $this;
    }

    public function removeTest(Tests $test): static
    {
        if ($this->tests->removeElement($test)) {
            if ($test->getGradeType() === $this) {
                $test->setGradeType(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
