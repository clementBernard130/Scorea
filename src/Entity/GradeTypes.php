<?php

namespace App\Entity;

use App\Repository\GradeTypesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeTypesRepository::class)]
class GradeTypes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gradeTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skills $skill = null;

    #[ORM\ManyToOne(inversedBy: 'gradeTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GradeTypeNames $type = null;

    #[ORM\Column]
    private ?int $weight = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkill(): ?Skills
    {
        return $this->skill;
    }

    public function setSkill(?Skills $skill): static
    {
        $this->skill = $skill;

        return $this;
    }

    public function getType(): ?GradeTypeNames
    {
        return $this->type;
    }

    public function setType(?GradeTypeNames $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }
}
