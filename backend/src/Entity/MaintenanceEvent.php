<?php

namespace App\Entity;

use App\Repository\MaintenanceEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceEventRepository::class)]
class MaintenanceEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $scheduledAt;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "boolean")]
    private bool $completed = false;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getScheduledAt(): \DateTimeInterface { return $this->scheduledAt; }
    public function setScheduledAt(\DateTimeInterface $dt): self { $this->scheduledAt = $dt; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): self { $this->description = $desc; return $this; }
    public function isCompleted(): bool { return $this->completed; }
    public function setCompleted(bool $completed): self { $this->completed = $completed; return $this; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeInterface $dt): self { $this->completedAt = $dt; return $this; }
}
