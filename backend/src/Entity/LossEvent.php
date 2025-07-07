<?php

namespace App\Entity;

use App\Repository\LossEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LossEventRepository::class)]
class LossEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrayRow::class, inversedBy: "lossEvents")]
    #[ORM\JoinColumn(nullable: false)]
    private ?TrayRow $trayRow = null;

    #[ORM\ManyToOne(targetEntity: LossReason::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?LossReason $reason = null;

    #[ORM\Column(type: "float")]
    private float $quantity;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $lostAt;

    public function getId(): ?int { return $this->id; }

    public function getTrayRow(): ?TrayRow { return $this->trayRow; }
    public function setTrayRow(?TrayRow $trayRow): self { $this->trayRow = $trayRow; return $this; }

    public function getReason(): ?LossReason { return $this->reason; }
    public function setReason(?LossReason $reason): self { $this->reason = $reason; return $this; }

    public function getQuantity(): float { return $this->quantity; }
    public function setQuantity(float $quantity): self { $this->quantity = $quantity; return $this; }

    public function getLostAt(): \DateTimeInterface { return $this->lostAt; }
    public function setLostAt(\DateTimeInterface $dt): self { $this->lostAt = $dt; return $this; }
}
