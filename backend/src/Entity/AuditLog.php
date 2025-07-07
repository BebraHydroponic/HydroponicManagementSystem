<?php

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $user;

    #[ORM\Column(type: "string", length: 100)]
    private string $action;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $entityType = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $entityId = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $performedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): self
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getPerformedAt(): \DateTimeInterface
    {
        return $this->performedAt;
    }

    public function setPerformedAt(\DateTimeInterface $performedAt): self
    {
        $this->performedAt = $performedAt;
        return $this;
    }

    // Alias for compatibility if you must keep getCreatedAt() in controller
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->performedAt;
    }

    public function setCreatedAt(\DateTimeInterface $dt): self
    {
        return $this->setPerformedAt($dt);
    }
}
