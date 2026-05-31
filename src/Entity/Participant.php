<?php

namespace App\Entity;

use App\Enum\ParticipantStar;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'participant')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PARTICIPANT', fields: ['player', 'tournament'])]
#[UniqueEntity(fields: ['player', 'tournament'], message: 'not_unique_participant')]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournament $tournament = null;

    #[ORM\Column(nullable: true)]
    private ?int $placement = null;

    #[ORM\Column(nullable: true, enumType: ParticipantStar::class)]
    private ?ParticipantStar $howWellPlayed = null;

    #[ORM\Column(nullable: true)]
    private ?int $rebuy = null;

    #[ORM\Column]
    private ?int $howMuchWon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getPlacement(): ?int
    {
        return $this->placement;
    }

    public function setPlacement(?int $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

    public function getHowWellPlayed(): ?ParticipantStar
    {
        return $this->howWellPlayed;
    }

    public function setHowWellPlayed(?ParticipantStar $howWellPlayed): static
    {
        $this->howWellPlayed = $howWellPlayed;

        return $this;
    }

    public function getRebuy(): ?int
    {
        return $this->rebuy;
    }

    public function setRebuy(?int $rebuy): static
    {
        $this->rebuy = $rebuy;

        return $this;
    }

    public function getHowMuchWon(): ?int
    {
        return $this->howMuchWon;
    }

    public function setHowMuchWon(int $howMuchWon): static
    {
        $this->howMuchWon = $howMuchWon;

        return $this;
    }
}
