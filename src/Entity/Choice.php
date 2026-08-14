<?php

namespace App\Entity;

use App\Repository\ChoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChoiceRepository::class)]
class Choice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["reponse:post"])] //, "choice:update"
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le label du choix est obligatoire')]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(["reponse:post"])] // , "choice:update"
    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[Assert\NotNull(message: 'Un choix doit être relié à un sondage')]
    #[ORM\ManyToOne(inversedBy: 'choices')]
    #[Groups(["reponse:post"])]
    private ?Sondage $whichPoll_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getWhichPoll(): ?Sondage
    {
        return $this->whichPoll_id;
    }

    public function setWhichPoll(?Sondage $whichPoll): static
    {
        $this->whichPoll_id = $whichPoll;

        return $this;
    }
}
