<?php

namespace App\Entity;

use App\Repository\InventoryLotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InventoryLotRepository::class)]
#[ORM\Table(name: 'inventory_lot')]
class InventoryLot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * @see config/validator/validation.yaml App\Entity\InventoryLot::productName
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $productName;

    /**
     * @see config/validator/validation.yaml App\Entity\InventoryLot::quantity
     */
    #[ORM\Column(type: 'float')]
    private float $quantity = 0;

    /**
     * @see config/validator/validation.yaml App\Entity\InventoryLot::location
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $location;

    /**
     * When this lot was created.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
