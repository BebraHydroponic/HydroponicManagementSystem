<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\Table(name: 'place')]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, TrayRow>
     */
    #[ORM\OneToMany(mappedBy: 'place', targetEntity: TrayRow::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $trayRows;

    public function __construct()
    {
        $this->trayRows = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, TrayRow>
     */
    public function getTrayRows(): Collection
    {
        return $this->trayRows;
    }

    public function addTrayRow(TrayRow $trayRow): self
    {
        if (!$this->trayRows->contains($trayRow)) {
            $this->trayRows->add($trayRow);
            $trayRow->setPlace($this);
        }
        return $this;
    }

    public function removeTrayRow(TrayRow $trayRow): self
    {
        if ($this->trayRows->removeElement($trayRow)) {
            // owning side (TrayRow) is not touched, so it remains at its last value
            // if you need to clear it, handle it in your business logic/service layer
        }
        return $this;
    }
}
