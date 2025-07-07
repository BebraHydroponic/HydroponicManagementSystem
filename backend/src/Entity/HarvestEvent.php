<?php

namespace App\Entity;

use App\Repository\HarvestEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HarvestEventRepository::class)]
#[ORM\Table(name: 'harvest_event')]
class HarvestEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * @see config/validator/validation.yaml App\Entity\HarvestEvent::batch
     */
    #[ORM\ManyToOne(targetEntity: Batch::class, inversedBy: 'harvestEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private Batch $batch;

    /**
     * @see config/validator/validation.yaml App\Entity\HarvestEvent::trayRow
     */
    #[ORM\ManyToOne(targetEntity: TrayRow::class, inversedBy: 'harvestEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private TrayRow $trayRow;

    /**
     * @see config/validator/validation.yaml App\Entity\HarvestEvent::quantity
     */
    #[ORM\Column(type: 'float')]
    private float $quantity;

    /**
     * @see config/validator/validation.yaml App\Entity\HarvestEvent::harvestedAt
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $harvestedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBatch(): Batch
    {
        return $this->batch;
    }

    public function setBatch(Batch $batch): self
    {
        $this->batch = $batch;
        return $this;
    }

    public function getTrayRow(): TrayRow
    {
        return $this->trayRow;
    }

    public function setTrayRow(TrayRow $trayRow): self
    {
        $this->trayRow = $trayRow;
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

    public function getHarvestedAt(): \DateTimeImmutable
    {
        return $this->harvestedAt;
    }

    public function setHarvestedAt(\DateTimeImmutable $harvestedAt): self
    {
        $this->harvestedAt = $harvestedAt;
        return $this;
    }
}
