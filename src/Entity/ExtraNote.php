<?php

namespace App\Entity;

use App\Repository\ExtraNoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExtraNoteRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ExtraNote
{

    const THESIS_TYPE      = 'Soutenance';
    const TRAINEESHIP_TYPE = 'Stage';
    const SEMINAR_TYPE     = 'Seminaire';

    static $typeList = [
        self::THESIS_TYPE      => 'SOUTENANCE',
        self::TRAINEESHIP_TYPE => 'STAGE',
        self::SEMINAR_TYPE     => 'SEMINAIRE'
    ];

    const STATUS_CREATED   = 'CREATED';
    const STATUS_VALIDATED = 'VALIDATED';
    const STATUS_REJECTED  = 'REJECTED';

    /**
     * @var array
     */
    static $statusList = [
        'Validé'  => self::STATUS_VALIDATED,
        'Rejetté' => self::STATUS_REJECTED,
    ];

    /**
     * @var array
     */
    static $cmStatusList = [
        'Vérifié' => 'CM_VERIFIED',
        'Validé'  => 'CM_VALIDATED',
        'Rejetté' => 'CM_REJECTED',
    ];

    /**
     * @var array
     */
    static $doyenStatusList = [
        'Vérifié' => 'DOYEN_VERIFIED',
        'Validé'  => 'DOYEN_VALIDATED',
        'Rejetté' => 'DOYEN_REJECTED',
    ];

    /**
     * @var array
     */
    static $recteurStatusList = [
        'Vérifié' => 'RECTEUR_VERIFIED',
        'Validé'  => 'RECTEUR_VALIDATED',
        'Rejetté' => 'RECTEUR_REJECTED',
    ];

    /**
     * @var array
     */
    static $sgStatusList = [
        'Vérifié' => 'SG_VERIFIED',
        'Validé'  => 'SG_VALIDATED',
        'Rejetté' => 'SG_REJECTED',
    ];

    /**
     * @var array
     */
    static $onlyCreatedStatus = [
        'Création' => self::STATUS_CREATED
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getId() : ?int
    {
        return $this->id;
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

    public function getNote() : ?string
    {
        return $this->note;
    }

    public function setNote(string $note) : self
    {
        $this->note = $note;

        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus() : ?string
    {
        return $this->status;
    }

    public function setStatus(string $status) : self
    {
        $this->status = $status;

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
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
        $this->setStatusValue();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateEvent()
    {
        $this->setUpdatedAtValue();
    }

    private function setStatusValue()
    {
        $this->status = !$this->status ? self::STATUS_CREATED : $this->status;
    }

    private function setCreatedAtValue()
    {
        $this->createdAt = !$this->createdAt ? new \DateTime() : $this->createdAt;
    }

    private function setUpdatedAtValue()
    {
        $this->updatedAt = !$this->updatedAt ? new \DateTime() : $this->updatedAt;
    }
}
