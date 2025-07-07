<?php

namespace App\Entity;

use App\Repository\InventoryTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryTransactionRepository::class)]
class InventoryTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InventoryLot::class, inversedBy: "transactions")]
    #[ORM\JoinColumn(nullable: false)]
    private ?InventoryLot $inventoryLot = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $type;

    #[ORM\Column(type: "float")]
    private float $quantity;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $notes = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    public function getId(): ?int { return $this->id; }
    public function getInventoryLot(): ?InventoryLot { return $this->inventoryLot; }
    public function setInventoryLot(?InventoryLot $lot): self { $this->inventoryLot = $lot; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getQuantity(): float { return $this->quantity; }
    public function setQuantity(float $qty): self { $this->quantity = $qty; return $this; }
    public function getNotes(): ?array { return $this->notes; }
    public function setNotes(?array $notes): self { $this->notes = $notes; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $dt): self { $this->createdAt = $dt; return $this; }
}
