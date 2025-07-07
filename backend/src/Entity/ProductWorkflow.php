<?php

namespace App\Entity;

use App\Repository\ProductWorkflowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductWorkflowRepository::class)]
class ProductWorkflow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $stage;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int { return $this->id; }
    public function getStage(): string { return $this->stage; }
    public function setStage(string $stage): self { $this->stage = $stage; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }
}

