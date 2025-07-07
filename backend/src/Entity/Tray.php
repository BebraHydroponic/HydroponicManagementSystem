<?php

namespace App\Entity;

use App\Repository\TrayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrayRepository::class)]
#[ORM\Table(name: 'tray')]
class Tray
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $code;

    /**
     * @var Collection<int, TrayRow>
     */
    #[ORM\OneToMany(mappedBy: 'tray', targetEntity: TrayRow::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $trayRows;

    public function __construct()
    {
        $this->trayRows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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
            $trayRow->setTray($this);
        }
        return $this;
    }

    public function removeTrayRow(TrayRow $trayRow): self
    {
        if ($this->trayRows->removeElement($trayRow)) {
            // owning side is unchanged—handle nullification if needed in your service logic
        }
        return $this;
    }
}
