<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProfilRepository::class)]
class Profil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["profil:read"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, name: "imageProfil", nullable: true)]
    #[Groups(["profil:read"])]
    #[Assert\Length(max: 255, maxMessage: 'Le nom de l\'image de profil est trop long')]
    private ?string $imageProfil = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["profil:read"])]
    #[Assert\Length(max: 255, maxMessage: 'La bio ne peut pas dépasser {{ limit }} caractères')]
    private ?string $bio = null;

    #[ORM\ManyToOne(inversedBy: 'profils')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["profil:read"])]
    #[Assert\NotNull(message: 'Un profil doit être associé à un utilisateur')]
    private ?User $user_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getImageProfil(): ?string
    {
        return $this->imageProfil;
    }

    public function setImageProfil(?string $imageProfil): static
    {
        $this->imageProfil = $imageProfil;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
