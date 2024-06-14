<?php

namespace App\Entity;

use App\Repository\FichePresenceEnseignantHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FichePresenceEnseignantHistoriqueRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class FichePresenceEnseignantHistorique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=FichePresenceEnseignant::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichePresenceEnseignant;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFichePresenceEnseignant(): ?FichePresenceEnseignant
    {
        return $this->fichePresenceEnseignant;
    }

    public function setFichePresenceEnseignant(?FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        $this->fichePresenceEnseignant = $fichePresenceEnseignant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }
    }

}
