<?php

namespace App\Entity;

use App\Repository\ConcoursEmploiDuTempsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConcoursEmploiDuTempsRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConcoursEmploiDuTemps
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Concours::class, inversedBy="concoursEmploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $concours;

    /**
     * @ORM\ManyToOne(targetEntity=ConcoursMatiere::class, inversedBy="concoursEmploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $concoursMatiere;

    /**
     * @ORM\ManyToOne(targetEntity=Salles::class, inversedBy="concoursEmploiDuTemps")
     */
    private $salle;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcours(): ?Concours
    {
        return $this->concours;
    }

    public function setConcours(?Concours $concours): self
    {
        $this->concours = $concours;

        return $this;
    }

    public function getConcoursMatiere(): ?ConcoursMatiere
    {
        return $this->concoursMatiere;
    }

    public function setConcoursMatiere(?ConcoursMatiere $concoursMatiere): self
    {
        $this->concoursMatiere = $concoursMatiere;

        return $this;
    }

    public function getSalle(): ?Salles
    {
        return $this->salle;
    }

    public function setSalle(?Salles $salle): self
    {
        $this->salle = $salle;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
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
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * @throws \Exception
     */
    private function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire): self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }
}
