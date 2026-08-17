<?php

namespace App\Entity;

use App\Repository\SondageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SondageRepository::class)]
class Sondage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sondage:read', 'sondage:update'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(max: 60,
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères')]
    #[ORM\Column(length: 255)]
    #[Groups(['sondage:read', 'sondage:update'])]
    private ?string $title = null;

    #[Assert\NotNull(message: 'Le statut actif/inactif est obligatoire')]
    #[ORM\Column]
    #[Groups(['sondage:read'])]
    private ?bool $isActive = null;

    #[Assert\NotBlank(message: 'La question est obligatoire')]
    #[Assert\Length(min: 10, max: 255,
        minMessage: 'La question doit contenir au moins {{ limit }} caractères',
        maxMessage: 'La question ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/\?$/', message: 'La question doit se terminer par un point d\'interrogation')]
    #[ORM\Column(length: 255)]
    #[Groups(['sondage:read', 'sondage:update'])]
    private ?string $question = null;

    #[Assert\NotNull(message: 'La date de création n\'est pas valide')]
    #[ORM\Column]
    #[Groups(['sondage:read'])]
    private ?\DateTimeImmutable $createAt = null;

    /* Surment remove
    #[Assert\Url(message: '\'{{ value }}\' n\'est pas une URL valide pour l\'image')]
    #[Assert\Length(max: 2000, maxMessage: 'L\'URL de l\'image ne peut pas dépasser {{ limit }} caractères')]
    */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['sondage:read', 'sondage:update'])]
    private ?string $image_url = null;

    #[ORM\ManyToOne(inversedBy: 'sondages')]
    #[Groups(['sondage:read'])]
    private ?User $whoMakeIt_id = null;

    /**
     * @var Collection<int, Choice>
     */
    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'whichPoll_id')]
    private Collection $choices;

    #[Assert\NotBlank(message: 'La catégorie est obligatoire')]
    #[Assert\Choice(choices: ['VS', 'Anime', 'Perso', 'OP/ED', 'Manga', 'Autre'],
        message: 'La catégorie \'{{ value }}\' n\'est pas valide. Choisissez parmi : {{ choices }}')]
    #[ORM\Column(length: 255)]
    #[Groups(['sondage:read', 'sondage:update'])]
    private ?string $category_name = null;


    public function __construct()
    {
        $this->choices = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getWhoMakeIt(): ?User
    {
        return $this->whoMakeIt_id;
    }

    public function setWhoMakeIt(?User $whoMakeIt): static
    {
        $this->whoMakeIt_id = $whoMakeIt;

        return $this;
    }

    /**
     * @return Collection<int, Choice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): static
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setWhichPoll($this);
        }

        return $this;
    }

    public function removeChoice(Choice $choice): static
    {
        if ($this->choices->removeElement($choice)) {
            // set the owning side to null (unless already changed)
            if ($choice->getWhichPoll() === $this) {
                $choice->setWhichPoll(null);
            }
        }

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function setCategoryName(string $category_name): static
    {
        $this->category_name = $category_name;

        return $this;
    }
}
