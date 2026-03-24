<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    /**
     * @var Collection<int, Sections>
     */
    #[ORM\ManyToMany(targetEntity: Sections::class, inversedBy: 'users')]
    private Collection $sections;

    /**
     * @var Collection<int, Tests>
     */
    #[ORM\OneToMany(targetEntity: Tests::class, mappedBy: 'teacher')]
    private Collection $tests;

    /**
     * @var Collection<int, Grades>
     */
    #[ORM\OneToMany(targetEntity: Grades::class, mappedBy: 'student')]
    private Collection $grades;

    /**
     * @var Collection<int, ApprenticeMentors>
     */
    #[ORM\OneToMany(targetEntity: ApprenticeMentors::class, mappedBy: 'apprentice')]
    private Collection $apprentice;

    /**
     * @var Collection<int, ApprenticeMentors>
     */
    #[ORM\OneToMany(targetEntity: ApprenticeMentors::class, mappedBy: 'mentor')]
    private Collection $mentor;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->grades = new ArrayCollection();
        $this->apprentice = new ArrayCollection();
        $this->mentor = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

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
        }

        return $this;
    }

    public function removeSection(Sections $section): static
    {
        $this->sections->removeElement($section);

        return $this;
    }

    public function __toString(): string
    {
        $firstName = (string) $this->first_name;
        $lastName = (string) $this->last_name;

        return trim($firstName . ' ' . $lastName);
    }

    /**
     * Get the color associated with the user's highest priority role.
     * Red (#dc3545) = ROLE_ADMIN
     * Purple (#6f42c1) = ROLE_TEACHER
     * Orange (#fd7e14) = ROLE_ASSISTANT
     * Blue (#0d6efd) = ROLE_STUDENT
     * Gray (#6c757d) = Default
     * 
     * @return string Hex color code
     */
    public function getRoleColor(): string
    {
        $roles = $this->getRoles();

        // Priority order: ADMIN > TEACHER > ASSISTANT > STUDENT
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return '#dc3545'; // Red
        }
        if (in_array('ROLE_TEACHER', $roles, true)) {
            return '#6f42c1'; // Purple
        }
        if (in_array('ROLE_ASSISTANT', $roles, true)) {
            return '#fd7e14'; // Orange
        }
        if (in_array('ROLE_STUDENT', $roles, true)) {
            return '#0d6efd'; // Blue
        }

        // Default color for ROLE_USER or unknown roles
        return '#6c757d'; // Gray
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
            $test->setTeacher($this);
        }

        return $this;
    }

    public function removeTest(Tests $test): static
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getTeacher() === $this) {
                $test->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Grades>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grades $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setStudent($this);
        }

        return $this;
    }

    public function removeGrade(Grades $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            // set the owning side to null (unless already changed)
            if ($grade->getStudent() === $this) {
                $grade->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApprenticeMentors>
     */
    public function getApprentice(): Collection
    {
        return $this->apprentice;
    }

    public function addApprentice(ApprenticeMentors $apprentice): static
    {
        if (!$this->apprentice->contains($apprentice)) {
            $this->apprentice->add($apprentice);
            $apprentice->setApprentice($this);
        }

        return $this;
    }

    public function removeApprentice(ApprenticeMentors $apprentice): static
    {
        if ($this->apprentice->removeElement($apprentice)) {
            // set the owning side to null (unless already changed)
            if ($apprentice->getApprentice() === $this) {
                $apprentice->setApprentice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApprenticeMentors>
     */
    public function getMentor(): Collection
    {
        return $this->mentor;
    }

    public function addMentor(ApprenticeMentors $mentor): static
    {
        if (!$this->mentor->contains($mentor)) {
            $this->mentor->add($mentor);
            $mentor->setMentor($this);
        }

        return $this;
    }

    public function removeMentor(ApprenticeMentors $mentor): static
    {
        if ($this->mentor->removeElement($mentor)) {
            // set the owning side to null (unless already changed)
            if ($mentor->getMentor() === $this) {
                $mentor->setMentor(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

}

