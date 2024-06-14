<?php

namespace App\Entity;

use App\Repository\EmploiDuTempsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=EmploiDuTempsRepository::class)
 */
class EmploiDuTemps
{

    const STATUS_CREATED            = 1;
    const STATUS_ASSIST_VALIDATED   = 2;
    const STATUS_CM_VALIDATED       = 3;
    const STATUS_COMPTA_VALIDATED   = 4;
    const STATUS_RF_VALIDATED       = 5;
    const STATUS_SG_VALIDATED       = 6;
    const STATUS_RECTEUR_VALIDATED  = 7;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="emploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $matiere;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Salles::class, inversedBy="emploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salles;

    /**
     * @ORM\Column(type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(type="time")
     */
    private $endTime;

    /**
     * @ORM\Column(type="date")
     */
    private $dateSchedule;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="emploiDuTemps")
     */
    private $niveau;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $videoprojecteur;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $connexion;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="emploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="emploiDuTemps")
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=UniteEnseignements::class, inversedBy="emploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ue;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="emploiDuTemps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $semestre;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $TroncCommun;

    /**
     * @ORM\Column(type="integer")
     */
    private $statut;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description_path;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="emploi_du_temps", orphanRemoval=true)
     */
    private $absences;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->absences = new ArrayCollection();
    }


    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        //$metadata->addPropertyConstraint('dateSchedule', new Assert\GreaterThanOrEqual('today'));
    }

    /**
    * @Assert\Callback
    */
   public function validate(ExecutionContextInterface $context, $payload)
   {
        $today = date("Y-m-d H:i:s");    
    //     if ($this->getDateSchedule()->format('Y-m-d H:i:s') < $today && (date('m') !== $this->getDateSchedule()->format('m'))) {
    //        $context->buildViolation('Vérifier la date')
    //            ->atPath('dateSchedule')
    //            ->addViolation();
    //    } 
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

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): self
    {
        $this->matiere = $matiere;

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

    public function getSalles(): ?Salles
    {
        return $this->salles;
    }

    public function setSalles(?Salles $salles): self
    {
        $this->salles = $salles;

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

    public function getDateSchedule(): ?\DateTimeInterface
    {
        return $this->dateSchedule;
    }

    public function setDateSchedule(\DateTimeInterface $dateSchedule): self
    {
        $this->dateSchedule = $dateSchedule;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getVideoprojecteur(): ?bool
    {
        return $this->videoprojecteur;
    }

    public function setVideoprojecteur(?bool $videoprojecteur): self
    {
        $this->videoprojecteur = $videoprojecteur;

        return $this;
    }

    public function getConnexion(): ?bool
    {
        return $this->connexion;
    }

    public function setConnexion(?bool $connexion): self
    {
        $this->connexion = $connexion;

        return $this;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getUe(): ?UniteEnseignements
    {
        return $this->ue;
    }

    public function setUe(?UniteEnseignements $ue): self
    {
        $this->ue = $ue;

        return $this;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
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

    public function getTroncCommun(): ?bool
    {
        return $this->TroncCommun;
    }

    public function setTroncCommun(?bool $TroncCommun): self
    {
        $this->TroncCommun = $TroncCommun;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionPath(): ?string
    {
        return $this->description_path;
    }

    public function setDescriptionPath(?string $description_path): self
    {
        $this->description_path = $description_path;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * @return Collection<int, Absences>
     */
    public function getAbsences(): Collection
    {
        return $this->absences;
    }

    public function addAbsence(Absences $absence): self
    {
        if (!$this->absences->contains($absence)) {
            $this->absences[] = $absence;
            $absence->setEmploiDuTemps($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getEmploiDuTemps() === $this) {
                $absence->setEmploiDuTemps(null);
            }
        }

        return $this;
    }
}
