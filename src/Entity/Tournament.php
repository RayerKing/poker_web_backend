<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?bool $isTrophy = null;

    #[ORM\Column]
    private ?bool $isEvent = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sendEmail = null;

    #[ORM\Column]
    private ?int $buyIn = null;

    #[ORM\Column]
    private ?bool $isRebuy = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'tournament', orphanRemoval: true)]
    private Collection $participants;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'tournament')]
    private Collection $medias;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->medias = new ArrayCollection();
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isTrophy(): ?bool
    {
        return $this->isTrophy;
    }

    public function setTrophy(bool $trophy): static
    {
        $this->isTrophy = $trophy;

        return $this;
    }

    public function isEvent(): ?bool
    {
        return $this->isEvent;
    }

    public function setEvent(bool $event): static
    {
        $this->isEvent = $event;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function isSendEmail(): ?bool
    {
        return $this->sendEmail;
    }

    public function setSendEmail(?bool $sendEmail): static
    {
        $this->sendEmail = $sendEmail;

        return $this;
    }

    public function getBuyIn(): ?int
    {
        return $this->buyIn;
    }

    public function setBuyIn(int $buyIn): static
    {
        $this->buyIn = $buyIn;

        return $this;
    }

    public function isRebuy(): ?bool
    {
        return $this->isRebuy;
    }

    public function setRebuy(bool $rebuy): static
    {
        $this->isRebuy = $rebuy;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setTournament($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getTournament() === $this) {
                $participant->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->medias;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->medias->contains($medium)) {
            $this->medias->add($medium);
            $medium->setTournament($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->medias->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getTournament() === $this) {
                $medium->setTournament(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function onNewTournament(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void 
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
