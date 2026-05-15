<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private ?bool $is_trophy = null;

    #[ORM\Column]
    private ?bool $is_event = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?bool $send_email = null;

    #[ORM\Column]
    private ?int $buy_in = null;

    #[ORM\Column]
    private ?bool $is_rebuy = null;

    /**
     * @var Collection<int, Participants>
     */
    #[ORM\OneToMany(targetEntity: Participants::class, mappedBy: 'tournament_id', orphanRemoval: true)]
    private Collection $participants;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'tournament_id')]
    private Collection $media;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->media = new ArrayCollection();
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
        return $this->is_trophy;
    }

    public function setIsTrophy(bool $is_trophy): static
    {
        $this->is_trophy = $is_trophy;

        return $this;
    }

    public function isEvent(): ?bool
    {
        return $this->is_event;
    }

    public function setIsEvent(bool $is_event): static
    {
        $this->is_event = $is_event;

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

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

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
        return $this->send_email;
    }

    public function setSendEmail(?bool $send_email): static
    {
        $this->send_email = $send_email;

        return $this;
    }

    public function getBuyIn(): ?int
    {
        return $this->buy_in;
    }

    public function setBuyIn(int $buy_in): static
    {
        $this->buy_in = $buy_in;

        return $this;
    }

    public function isRebuy(): ?bool
    {
        return $this->is_rebuy;
    }

    public function setIsRebuy(bool $is_rebuy): static
    {
        $this->is_rebuy = $is_rebuy;

        return $this;
    }

    /**
     * @return Collection<int, Participants>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participants $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setTournamentId($this);
        }

        return $this;
    }

    public function removeParticipant(Participants $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getTournamentId() === $this) {
                $participant->setTournamentId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setTournamentId($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getTournamentId() === $this) {
                $medium->setTournamentId(null);
            }
        }

        return $this;
    }
}
