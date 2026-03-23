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

    #[ORM\Column]
    private ?bool $isCertificative = null;


    /**
     * @var Collection<int, GradeTypes>
     */
    #[ORM\OneToMany(targetEntity: GradeTypes::class, mappedBy: 'type', orphanRemoval: true)]
    private Collection $gradeTypes;

    public function __construct()
    {
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

    public function isCertificative(): ?bool
    {
        return $this->isCertificative;
    }

    public function setIsCertificative(bool $isCertificative): static
    {
        $this->isCertificative = $isCertificative;

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
}
