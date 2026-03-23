<?php

namespace App\Entity;

use App\Repository\ApprenticeMentorsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApprenticeMentorsRepository::class)]
class ApprenticeMentors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'apprentice')]
    private ?Users $apprentice = null;

    #[ORM\ManyToOne(inversedBy: 'mentor')]
    private ?Users $mentor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApprentice(): ?Users
    {
        return $this->apprentice;
    }

    public function setApprentice(?Users $apprentice): static
    {
        $this->apprentice = $apprentice;

        return $this;
    }

    public function getMentor(): ?Users
    {
        return $this->mentor;
    }

    public function setMentor(?Users $mentor): static
    {
        $this->mentor = $mentor;

        return $this;
    }
}
