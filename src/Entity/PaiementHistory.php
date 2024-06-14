<?php

namespace App\Entity;

use App\Repository\PaiementHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaiementHistoryRepository::class)
 */
class PaiementHistory
{
    const PRESTATION_RESOURCE   = 'PRESTATION';
    const VACATION_RESOURCE     = 'VACATION';
    const SURVEILLANCE_RESOURCE = 'SURVEILLANCE';
    const ECOLAGE_RESOURCE = 'ECOLAGE';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $resource_name;

    /**
     * @ORM\Column(type="integer")
     */
    private $resource_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="paiementHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $validator;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $statut;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $montant;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourceName(): ?string
    {
        return $this->resource_name;
    }

    public function setResourceName(string $resource_name): self
    {
        $this->resource_name = $resource_name;

        return $this;
    }

    public function getResourceId(): ?int
    {
        return $this->resource_id;
    }

    public function setResourceId(int $resource_id): self
    {
        $this->resource_id = $resource_id;

        return $this;
    }

    public function getValidator(): ?User
    {
        return $this->validator;
    }

    public function setValidator(?User $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
