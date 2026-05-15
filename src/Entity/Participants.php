<?php

namespace App\Entity;

use App\Enum\ParticipantsStar;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantsRepository::class)]
class Participants
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player_id = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournament $tournament_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $placement = null;

    #[ORM\Column(nullable: true, enumType: ParticipantsStar::class)]
    private ?ParticipantsStar $how_well_played = null;

    #[ORM\Column(nullable: true)]
    private ?int $rebuy = null;

    #[ORM\Column]
    private ?int $how_much_won = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerId(): ?Player
    {
        return $this->player_id;
    }

    public function setPlayerId(?Player $player_id): static
    {
        $this->player_id = $player_id;

        return $this;
    }

    public function getTournamentId(): ?Tournament
    {
        return $this->tournament_id;
    }

    public function setTournamentId(?Tournament $tournament_id): static
    {
        $this->tournament_id = $tournament_id;

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

    public function getHowWellPlayed(): ?ParticipantsStar
    {
        return $this->how_well_played;
    }

    public function setHowWellPlayed(?ParticipantsStar $how_well_played): static
    {
        $this->how_well_played = $how_well_played;

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
        return $this->how_much_won;
    }

    public function setHowMuchWon(int $how_much_won): static
    {
        $this->how_much_won = $how_much_won;

        return $this;
    }
}
