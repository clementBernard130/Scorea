<?php

namespace App\Entity;

use App\Repository\AlertsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlertsRepository::class)]
class Alerts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?array $type = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'alerts')]
    private ?Subjects $subject = null;

    #[ORM\ManyToOne(inversedBy: 'alerts')]
    private ?Users $users = null;

    #[ORM\ManyToOne]
    private ?Tests $test = null;

    #[ORM\ManyToOne]
    private ?Sections $section = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?array
    {
        return $this->type;
    }

    public function setType(?array $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSubject(): ?Subjects
    {
        return $this->subject;
    }

    public function setSubject(?Subjects $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function getTest(): ?Tests
    {
        return $this->test;
    }

    public function setTest(?Tests $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getSection(): ?Sections
    {
        return $this->section;
    }

    public function setSection(?Sections $section): static
    {
        $this->section = $section;

        return $this;
    }
}
