<?php

namespace App\Entity;

use App\Repository\AnneeUniversitaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnneeUniversitaireRepository::class)
 */
class AnneeUniversitaire
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\Column(type="integer")
     */
    private $annee;

    /**
     * @ORM\OneToMany(targetEntity=Inscription::class, mappedBy="anneeUniversitaire")
     */
    private $inscriptions;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\OneToMany(targetEntity=Prestation::class, mappedBy="annee_universitaire", orphanRemoval=true)
     */
    private $prestations;

    /**
     * @ORM\OneToMany(targetEntity=Concours::class, mappedBy="annee_universitaire", orphanRemoval=true)
     */
    private $concours;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursConfig::class, mappedBy="annee_universitaire", orphanRemoval=true)
     */
    private $concoursConfigs;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->concours = new ArrayCollection();
        $this->concoursConfigs = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     *
     * @return \App\Entity\AnneeUniversitaire
     */
    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    /**
     * @param int $annee
     *
     * @return \App\Entity\AnneeUniversitaire
     */
    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * @return Collection|Inscription[]
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions[] = $inscription;
            $inscription->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getAnneeUniversitaire() === $this) {
                $inscription->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection|Prestation[]
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
            $prestation->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getAnneeUniversitaire() === $this) {
                $prestation->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Concours[]
     */
    public function getConcours(): Collection
    {
        return $this->concours;
    }

    public function addConcour(Concours $concour): self
    {
        if (!$this->concours->contains($concour)) {
            $this->concours[] = $concour;
            $concour->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removeConcour(Concours $concour): self
    {
        if ($this->concours->removeElement($concour)) {
            // set the owning side to null (unless already changed)
            if ($concour->getAnneeUniversitaire() === $this) {
                $concour->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConcoursConfig>
     */
    public function getConcoursConfigs(): Collection
    {
        return $this->concoursConfigs;
    }

    public function addConcoursConfig(ConcoursConfig $concoursConfig): self
    {
        if (!$this->concoursConfigs->contains($concoursConfig)) {
            $this->concoursConfigs[] = $concoursConfig;
            $concoursConfig->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removeConcoursConfig(ConcoursConfig $concoursConfig): self
    {
        if ($this->concoursConfigs->removeElement($concoursConfig)) {
            // set the owning side to null (unless already changed)
            if ($concoursConfig->getAnneeUniversitaire() === $this) {
                $concoursConfig->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }
}
