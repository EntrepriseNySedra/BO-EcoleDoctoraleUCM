<?php

namespace App\Entity;

use App\Repository\UniteEnseignementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=UniteEnseignementsRepository::class)
 */
class UniteEnseignements
{
    const TYPE_FONDAMENRALE = 'FONDAMENTALE';
    const TYPE_COMPLEMENTAIRE = 'COMPLEMENTAIRE';
    const TYPE_TRANSVERSALE = 'TRANSVERSALE';
    
    //List of type
    static $typeList = [
        'UE Fondamentales'      => 'FONDAMENTALE',
        'UE Complémentaires'    => 'COMPLEMENTAIRE',
        'UE Transversales'      => 'TRANSVERSALE'
    ];

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="uniteEnseignements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class, inversedBy="uniteEnseignements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $semestre;

    /**
     * @ORM\OneToMany(targetEntity=Matiere::class, mappedBy="uniteEnseignements", orphanRemoval=true)
     */
    private $matieres;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="uniteEnseignements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parcours;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $responsable;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $volumeHoraireTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $etudeTheorique;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $travauDirige;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $credit;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="ue")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="ue", orphanRemoval=true)
     */
    private $absences;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="ue", orphanRemoval=true)
     */
    private $emploiDuTemps;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coefficient;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->matieres = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    /**
     * @return Collection|Matiere[]
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    public function addMatiere(Matiere $matiere): self
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres[] = $matiere;
            $matiere->setUniteEnseignements($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getUniteEnseignements() === $this) {
                $matiere->setUniteEnseignements(null);
            }
        }

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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable): self
    {
        $this->responsable = $responsable;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getVolumeHoraireTotal(): ?int
    {
        return $this->volumeHoraireTotal;
    }

    public function setVolumeHoraireTotal(?int $volumeHoraireTotal): self
    {
        $this->volumeHoraireTotal = $volumeHoraireTotal;

        return $this;
    }

    public function getEtudeTheorique(): ?int
    {
        return $this->etudeTheorique;
    }

    public function setEtudeTheorique(?int $etudeTheorique): self
    {
        $this->etudeTheorique = $etudeTheorique;

        return $this;
    }

    public function getTravauDirige(): ?int
    {
        return $this->travauDirige;
    }

    public function setTravauDirige(?int $travauDirige): self
    {
        $this->travauDirige = $travauDirige;

        return $this;
    }

    public function getCredit(): ?int
    {
        return $this->credit;
    }

    public function setCredit(?int $credit): self
    {
        $this->credit = $credit;

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
            $fichePresenceEnseignant->setUe($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getUe() === $this) {
                $fichePresenceEnseignant->setUe(null);
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
            $absence->setUe($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getUe() === $this) {
                $absence->setUe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EmploiDuTemps[]
     */
    public function getEmploiDuTemps(): Collection
    {
        return $this->emploiDuTemps;
    }

    public function addEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if (!$this->emploiDuTemps->contains($emploiDuTemp)) {
            $this->emploiDuTemps[] = $emploiDuTemp;
            $emploiDuTemp->setUe($this);
        }

        return $this;
    }

    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getUe() === $this) {
                $emploiDuTemp->setUe(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
    }

    public function getCoefficient(): ?int
    {
        return $this->coefficient;
    }

    public function setCoefficient(?int $coefficient): self
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
