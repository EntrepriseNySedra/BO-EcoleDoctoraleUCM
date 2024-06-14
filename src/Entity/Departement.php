<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DepartementRepository::class)
 */
class Departement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, unique=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Mention::class, mappedBy="departement", orphanRemoval=true)
     */
    private $mentions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="domaine")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="domaine", orphanRemoval=true)
     */
    private $absences;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->mentions = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
        $this->absences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Mention[]
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addMention(Mention $mention): self
    {
        if (!$this->mentions->contains($mention)) {
            $this->mentions[] = $mention;
            $mention->setDepartement($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->removeElement($mention)) {
            // set the owning side to null (unless already changed)
            if ($mention->getDepartement() === $this) {
                $mention->setDepartement(null);
            }
        }

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * @return Collection|FichePresenceEnseignant[]
     */
    public function getFichePresenceEnseignants(): Collection
    {
        return $this->fichePresenceEnseignants;
    }

    public function addFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if (!$this->fichePresenceEnseignants->contains($fichePresenceEnseignant)) {
            $this->fichePresenceEnseignants[] = $fichePresenceEnseignant;
            $fichePresenceEnseignant->setDomaine($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getDomaine() === $this) {
                $fichePresenceEnseignant->setDomaine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Absences[]
     */
    public function getAbsences(): Collection
    {
        return $this->absences;
    }

    public function addAbsence(Absences $absence): self
    {
        if (!$this->absences->contains($absence)) {
            $this->absences[] = $absence;
            $absence->setDomaine($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getDomaine() === $this) {
                $absence->setDomaine(null);
            }
        }

        return $this;
    }
}
