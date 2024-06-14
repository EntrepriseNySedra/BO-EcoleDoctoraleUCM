<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InscriptionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Inscription
{
    const STATUS_CREATED = 'CREATED';
    const STATUS_VALIDATED = 'VALIDATED';
    const STATUS_REFUSED = 'REFUSED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class, inversedBy="inscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class, inversedBy="inscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $payementRef;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payementRefDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payementRefPath;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class)
     */
    private $Niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class)
     */
    private $Parcours;

    /**
     * @ORM\OneToOne(targetEntity=FraisScolarite::class, inversedBy="inscription", cascade={"persist", "remove"})
     */
    private $frais_scolarite;

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

    public function getPayementRef() : ?string
    {
        return $this->payementRef;
    }

    public function setPayementRef(string $payementRef) : self
    {
        $this->payementRef = $payementRef;

        return $this;
    }

    public function getPayementRefDate() : ?\DateTimeInterface
    {
        return $this->payementRefDate;
    }

    public function setPayementRefDate(?\DateTimeInterface $payementRefDate) : self
    {
        $this->payementRefDate = $payementRefDate;

        return $this;
    }

    public function getPayementRefPath() : ?string
    {
        return $this->payementRefPath;
    }

    public function setPayementRefPath(?string $payementRefPath) : self
    {
        $this->payementRefPath = $payementRefPath;

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

    public function getStatus() : ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status) : self
    {
        $this->status = $status;

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

    private function setStatusValue()
    {
        $this->status = self::STATUS_CREATED;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->Niveau;
    }

    public function setNiveau(?Niveau $Niveau): self
    {
        $this->Niveau = $Niveau;

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->Parcours;
    }

    public function setParcours(?Parcours $Parcours): self
    {
        $this->Parcours = $Parcours;

        return $this;
    }

    public function getFraisScolarite(): ?FraisScolarite
    {
        return $this->frais_scolarite;
    }

    public function setFraisScolarite(?FraisScolarite $frais_scolarite): self
    {
        $this->frais_scolarite = $frais_scolarite;

        return $this;
    }
}
