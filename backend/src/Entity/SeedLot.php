<?php

namespace App\Entity;

use App\Repository\SeedLotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeedLotRepository::class)]
class SeedLot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $lotCode;

    #[ORM\Column(type: "string", length: 100)]
    private string $variety;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Supplier $supplier = null;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $expiryDate;

    public function getId(): ?int { return $this->id; }
    public function getLotCode(): string { return $this->lotCode; }
    public function setLotCode(string $code): self { $this->lotCode = $code; return $this; }
    public function getVariety(): string { return $this->variety; }
    public function setVariety(string $variety): self { $this->variety = $variety; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $qty): self { $this->quantity = $qty; return $this; }
    public function getSupplier(): ?Supplier { return $this->supplier; }
    public function setSupplier(?Supplier $supplier): self { $this->supplier = $supplier; return $this; }
    public function getExpiryDate(): \DateTimeInterface { return $this->expiryDate; }
    public function setExpiryDate(\DateTimeInterface $dt): self { $this->expiryDate = $dt; return $this; }
}
