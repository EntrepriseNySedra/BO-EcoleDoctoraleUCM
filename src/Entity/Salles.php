<?php

namespace App\Entity;

use App\Repository\SallesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=SallesRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Salles
{

    const STATUS_DISABLED = 0;

    const STATUS_ENABLED = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="salles")
     */
    private $emploiDuTemps;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursEmploiDuTemps::class, mappedBy="salle")
     */
    private $concoursEmploiDuTemps;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capacite;

    /**
     * @ORM\ManyToOne(targetEntity=Batiment::class, inversedBy="salles")
     */
    private $batiment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $internetConnexionOn;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $videoProjecteurOn;

    /**
     * Salles constructor.
     */
    public function __construct()
    {
        $this->emploiDuTemps = new ArrayCollection();
        $this->concoursEmploiDuTemps = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUuid() : ?string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return \App\Entity\Salles
     */
    public function setUuid(string $uuid) : self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLibelle() : ?string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     *
     * @return \App\Entity\Salles
     */
    public function setLibelle(string $libelle) : self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getStatus() : ?bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     *
     * @return \App\Entity\Salles
     */
    public function setStatus(bool $status) : self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|EmploiDuTemps[]
     */
    public function getEmploiDuTemps() : Collection
    {
        return $this->emploiDuTemps;
    }

    /**
     * @param \App\Entity\EmploiDuTemps $emploiDuTemp
     *
     * @return \App\Entity\Salles
     */
    public function addEmploiDuTemp(EmploiDuTemps $emploiDuTemp) : self
    {
        if (!$this->emploiDuTemps->contains($emploiDuTemp)) {
            $this->emploiDuTemps[] = $emploiDuTemp;
            $emploiDuTemp->setSalles($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\EmploiDuTemps $emploiDuTemp
     *
     * @return \App\Entity\Salles
     */
    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp) : self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getSalles() === $this) {
                $emploiDuTemp->setSalles(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return \App\Entity\Salles
     */
    public function setCreatedAt(\DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return \App\Entity\Salles
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();

        $this->uuid   = Uuid::uuid4();
        $this->status = !$this->status ? self::STATUS_ENABLED : $this->status;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateEvent()
    {
        $this->setUpdatedAtValue();
    }

    /**
     * @throws \Exception
     */
    private function setCreatedAtValue()
    {
        $this->createdAt = !$this->createdAt ? new \DateTime() : $this->createdAt;
    }

    /**
     * @throws \Exception
     */
    private function setUpdatedAtValue()
    {
        $this->updatedAt = !$this->updatedAt ? new \DateTime() : $this->updatedAt;
    }

    /**
     * @return Collection|ConcoursEmploiDuTemps[]
     */
    public function getConcoursEmploiDuTemps(): Collection
    {
        return $this->concoursEmploiDuTemps;
    }

    public function addConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if (!$this->concoursEmploiDuTemps->contains($concoursEmploiDuTemp)) {
            $this->concoursEmploiDuTemps[] = $concoursEmploiDuTemp;
            $concoursEmploiDuTemp->setSalle($this);
        }

        return $this;
    }

    public function removeConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if ($this->concoursEmploiDuTemps->removeElement($concoursEmploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($concoursEmploiDuTemp->getSalle() === $this) {
                $concoursEmploiDuTemp->setSalle(null);
            }
        }

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(?int $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getBatiment(): ?Batiment
    {
        return $this->batiment;
    }

    public function setBatiment(?Batiment $batiment): self
    {
        $this->batiment = $batiment;

        return $this;
    }

    public function getInternetConnexionOn(): ?bool
    {
        return $this->internetConnexionOn;
    }

    public function setInternetConnexionOn(?bool $internetConnexionOn): self
    {
        $this->internetConnexionOn = $internetConnexionOn;

        return $this;
    }

    public function getVideoProjecteurOn(): ?bool
    {
        return $this->videoProjecteurOn;
    }

    public function setVideoProjecteurOn(?bool $videoProjecteurOn): self
    {
        $this->videoProjecteurOn = $videoProjecteurOn;

        return $this;
    }
}
