<?php

namespace App\Entity;

use App\Repository\ReponsesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponsesRepository::class)]
class Reponses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["reponse:create"])]
    private ?int $id = null;

    #[Assert\NotNull(message: 'Un utilisateur doit être associé à la réponse')]
    #[Groups(["reponse:create"])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $user_id = null;

    #[Assert\NotNull(message: 'Un choix doit être associé à la réponse')]
    #[Groups(["reponse:create"])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Choice $choice_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getChoiceId(): ?Choice
    {
        return $this->choice_id;
    }

    public function setChoiceId(?Choice $choice_id): static
    {
        $this->choice_id = $choice_id;

        return $this;
    }
}
