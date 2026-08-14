<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user:read_id"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user:read_pseudo"])]
    #[Assert\NotBlank(message: 'Un pseudonyme est obligatoire')]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user:read_email"])]
    #[Assert\NotBlank(message: 'Un email est obligatoire')]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(min: 10, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères')]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(["user:read_date"])]
    #[Assert\NotNull(message: 'Une date de création est nécessaire')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(["user:read_role"])]
    private array $role = [];

    /**
     * @var Collection<int, Profil>
     */
    #[ORM\OneToMany(targetEntity: Profil::class, mappedBy: 'user_id')]
    private Collection $profils;

    /**
     * @var Collection<int, Sondage>
     */
    #[ORM\OneToMany(targetEntity: Sondage::class, mappedBy: 'whoMakeIt_id')]
    private Collection $sondages;

    public function __construct()
    {
        $this->profils = new ArrayCollection();
        $this->sondages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->role;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRole(array $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function eraseCredentials(): void
    {
        // Laissez vide si aucune donné sensible temporaire
    }

    /**
     * @return Collection<int, Profil>
     */
    public function getProfils(): Collection
    {
        return $this->profils;
    }

    public function addProfil(Profil $profil): static
    {
        if (!$this->profils->contains($profil)) {
            $this->profils->add($profil);
            $profil->setUserId($this);
        }

        return $this;
    }

    public function removeProfil(Profil $profil): static
    {
        if ($this->profils->removeElement($profil)) {
            // set the owning side to null (unless already changed)
            if ($profil->getUserId() === $this) {
                $profil->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sondage>
     */
    public function getSondages(): Collection
    {
        return $this->sondages;
    }

    public function addSondage(Sondage $sondage): static
    {
        if (!$this->sondages->contains($sondage)) {
            $this->sondages->add($sondage);
            $sondage->setWhoMakeIt($this);
        }

        return $this;
    }

    public function removeSondage(Sondage $sondage): static
    {
        if ($this->sondages->removeElement($sondage)) {
            // set the owning side to null (unless already changed)
            if ($sondage->getWhoMakeIt() === $this) {
                $sondage->setWhoMakeIt(null);
            }
        }

        return $this;
    }
}
