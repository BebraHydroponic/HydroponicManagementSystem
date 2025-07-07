<?php

namespace App\Entity;

use App\Repository\TrayRowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrayRowRepository::class)]
#[ORM\Table(name: 'tray_row')]
class TrayRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Batch::class, inversedBy: 'trayRows')]
    #[ORM\JoinColumn(nullable: false)]
    private Batch $batch;

    #[ORM\ManyToOne(targetEntity: Tray::class, inversedBy: 'trayRows')]
    #[ORM\JoinColumn(nullable: false)]
    private Tray $tray;

    #[ORM\Column(type: 'integer')]
    private int $rowIndex;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Place $place;

    #[ORM\Column(type: 'float')]
    private float $quantity = 0;

    /**
     * @var Collection<int, HarvestEvent>
     */
    #[ORM\OneToMany(mappedBy: 'trayRow', targetEntity: HarvestEvent::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $harvestEvents;

    public function __construct()
    {
        $this->harvestEvents = new ArrayCollection();
    }

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

    public function getTray(): Tray
    {
        return $this->tray;
    }

    public function setTray(Tray $tray): self
    {
        $this->tray = $tray;
        return $this;
    }

    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    public function setRowIndex(int $rowIndex): self
    {
        $this->rowIndex = $rowIndex;
        return $this;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): self
    {
        $this->place = $place;
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

    /**
     * @return Collection<int, HarvestEvent>
     */
    public function getHarvestEvents(): Collection
    {
        return $this->harvestEvents;
    }

    public function addHarvestEvent(HarvestEvent $event): self
    {
        if (!$this->harvestEvents->contains($event)) {
            $this->harvestEvents->add($event);
            $event->setTrayRow($this);
        }
        return $this;
    }

    public function removeHarvestEvent(HarvestEvent $event): self
    {
        if ($this->harvestEvents->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getTrayRow() === $this) {
                $event->setTrayRow($event->getTrayRow());
            }
        }
        return $this;
    }
}
