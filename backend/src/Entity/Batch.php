<?php

namespace App\Entity;

use App\Repository\BatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatchRepository::class)]
#[ORM\Table(name: 'batch')]
class Batch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $sowDate;

    #[ORM\Column(type: 'boolean')]
    private bool $closed = false;

    /**
     * @var Collection<int, HarvestEvent>
     */
    #[ORM\OneToMany(mappedBy: 'batch', targetEntity: HarvestEvent::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $harvestEvents;

    public function __construct()
    {
        $this->harvestEvents = new ArrayCollection();
        $this->sowDate = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSowDate(): \DateTimeImmutable
    {
        return $this->sowDate;
    }

    public function setSowDate(\DateTimeImmutable $sowDate): self
    {
        $this->sowDate = $sowDate;
        return $this;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;
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
            $event->setBatch($this);
        }
        return $this;
    }

    public function removeHarvestEvent(HarvestEvent $event): self
    {
        if ($this->harvestEvents->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getBatch() === $this) {
                $event->setBatch($event->getBatch());
            }
        }
        return $this;
    }
}
