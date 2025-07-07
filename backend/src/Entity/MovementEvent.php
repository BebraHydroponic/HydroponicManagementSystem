<?php

namespace App\Entity;

use App\Repository\MovementEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovementEventRepository::class)]
class MovementEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrayRow::class, inversedBy: "movementEvents")]
    #[ORM\JoinColumn(nullable: false)]
    private ?TrayRow $trayRow = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $toPlace = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $movedAt;

    public function getId(): ?int { return $this->id; }

    public function getTrayRow(): ?TrayRow { return $this->trayRow; }
    public function setTrayRow(?TrayRow $trayRow): self { $this->trayRow = $trayRow; return $this; }

    public function getToPlace(): ?Place { return $this->toPlace; }
    public function setToPlace(?Place $toPlace): self { $this->toPlace = $toPlace; return $this; }

    public function getMovedAt(): \DateTimeInterface { return $this->movedAt; }
    public function setMovedAt(\DateTimeInterface $dt): self { $this->movedAt = $dt; return $this; }
}
