<?php

namespace App\Entity;

use App\Repository\CalendrierSoutenanceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalendrierSoutenanceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CalendrierSoutenance
{

    /**
     *
     */
    const STATUS_CREATED = 'CREATED';

    /**
     *
     */
    const STATUS_VALIDATED = 'VALIDATED';

    /**
     *
     */
    const STATUS_REJECTED = 'REJECTED';

    /**
     * @var array
     */
    static $cmStatusList = [
        'Validé'  => 'CM_VALIDATED',
        'Rejetté' => 'CM_REJECTED',
    ];

    /**
     * @var array
     */
    static $onlyCreatedStatus = [
        'Création'  => self::STATUS_CREATED
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class)
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="date")
     */
    private $dateSchedule;

    /**
     * @ORM\Column(type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(type="time")
     */
    private $endTime;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getLibelle() : ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle) : self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function getEtudiant() : ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant) : self
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    public function getAnneeUniversitaire() : ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire) : self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

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
        $this->setStatusValue();
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateEvent()
    {
        $this->setStatusValue();
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

    private function setStatusValue()
    {
        $this->status = !$this->status ? self::STATUS_CREATED : $this->status;
    }

    public function getStatus() : ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status) : self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateSchedule(): ?\DateTimeInterface
    {
        return $this->dateSchedule;
    }

    public function setDateSchedule(\DateTimeInterface $dateSchedule): self
    {
        $this->dateSchedule = $dateSchedule;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }
}
