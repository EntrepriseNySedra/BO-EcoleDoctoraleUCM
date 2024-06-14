<?php

namespace App\Entity;

use App\Repository\CalendrierExamenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\Services\WorkflowStatutService;

/**
 * @ORM\Entity(repositoryClass=CalendrierExamenRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CalendrierExamen
{

    /**
     *
     */
    const STATUS_CREATED = WorkflowStatutService::STATUS_CREATED;

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
     * @ORM\Column(type="string", length=45)
     */
    private $libelle;

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

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class)
     */
    private $departement;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class)
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class)
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=UniteEnseignements::class)
     * @Assert\NotBlank(message="Champ obligatoire")
     */
    private $uniteEnseignements;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class)
     * @Assert\NotBlank(message="Champ obligatoire")
     */
    private $matiere;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $statut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class)
     * @Assert\NotBlank(message="Champ obligatoire")
     */
    private $semestre;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="calendrierExamens")
     */
    private $surveillant;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="calendrierExamens")
     */
    private $auteur;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tronc_commun;

    /**
     * @ORM\OneToMany(targetEntity=CalendrierExamenSurveillance::class, mappedBy="calendrier_examen", orphanRemoval=true)
     */
    private $calendrierExamenSurveillances;

    public function __construct()
    {
        $this->calendrierExamenSurveillances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

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

    public function getUniteEnseignements(): ?UniteEnseignements
    {
        return $this->uniteEnseignements;
    }

    public function setUniteEnseignements(?UniteEnseignements $uniteEnseignements): self
    {
        $this->uniteEnseignements = $uniteEnseignements;

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

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
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        $this->setStatutValue();
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateEvent()
    {
        $this->setStatutValue();
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

    private function setStatutValue()
    {
        $this->statut = !$this->statut ? self::STATUS_CREATED : $this->statut;
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

    public function getSurveillant(): ?User
    {
        return $this->surveillant;
    }

    public function setSurveillant(?User $surveillant): self
    {
        $this->surveillant = $surveillant;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getTroncCommun(): ?int
    {
        return $this->tronc_commun;
    }

    public function setTroncCommun(?int $tronc_commun): self
    {
        $this->tronc_commun = $tronc_commun;

        return $this;
    }

    /**
     * @return Collection<int, CalendrierExamenSurveillance>
     */
    public function getCalendrierExamenSurveillances(): Collection
    {
        return $this->calendrierExamenSurveillances;
    }

    public function addCalendrierExamenSurveillance(CalendrierExamenSurveillance $calendrierExamenSurveillance): self
    {
        if (!$this->calendrierExamenSurveillances->contains($calendrierExamenSurveillance)) {
            $this->calendrierExamenSurveillances[] = $calendrierExamenSurveillance;
            $calendrierExamenSurveillance->setCalendrierExamen($this);
        }

        return $this;
    }

    public function removeCalendrierExamenSurveillance(CalendrierExamenSurveillance $calendrierExamenSurveillance): self
    {
        if ($this->calendrierExamenSurveillances->removeElement($calendrierExamenSurveillance)) {
            // set the owning side to null (unless already changed)
            if ($calendrierExamenSurveillance->getCalendrierExamen() === $this) {
                $calendrierExamenSurveillance->setCalendrierExamen(null);
            }
        }

        return $this;
    }
}
