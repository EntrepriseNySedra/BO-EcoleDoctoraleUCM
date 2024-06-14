<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=MatiereRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Matiere
{
    const IMPOT = 0.2;

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
     * @ORM\Column(type="string", length=255)
     */
    private $nom;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=UniteEnseignements::class, inversedBy="matieres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $uniteEnseignements;

    /**
     * @ORM\OneToMany(targetEntity=Cours::class, mappedBy="matiere")
     */
    private $cours;

    /**

     * @ORM\OneToMany(targetEntity=Notes::class, mappedBy="matiere")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="matiere")
     */
    private $emploiDuTemps;

    /**
     * @ORM\ManyToOne(targetEntity=Enseignant::class, inversedBy="matieres")
     */
    private $enseignant;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $volumeHoraire;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMatiere::class, mappedBy="matiere")
     */
    private $enseignantMatieres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="matiere")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $taux_horaire;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $syllabus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    public function __construct()
    {
        $this->uuid  = Uuid::uuid4();
        $this->cours = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
        $this->enseignantMatieres = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUuid() : ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid) : self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNom() : ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom) : self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCredit() : ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit) : self
    {
        $this->credit = $credit;

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

    public function getUniteEnseignements() : ?UniteEnseignements
    {
        return $this->uniteEnseignements;
    }

    public function setUniteEnseignements(?UniteEnseignements $uniteEnseignements) : self
    {
        $this->uniteEnseignements = $uniteEnseignements;

        return $this;
    }

    /**
     * @return Collection|Cours[]
     */
    public function getCours() : Collection
    {
        return $this->cours;
    }

    public function addCours(Cours $cour) : self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours[] = $cour;
            $cour->setMatiere($this);
        }

        return $this;
    }

    public function removeCours(Cours $cour) : self
    {
        if ($this->cours->removeElement($cour)) {
            // set the owning side to null (unless already changed)
            if ($cour->getMatiere() === $this) {
                $cour->setMatiere(null);
            }
        }

        return $this;
    }

    /**

     * @return Collection|Notes[]
     */
    public function getNotes() : Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note) : self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setMatiere($this);
        }

        return $this;
    }

    public function removeNote(Notes $note) : self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getMatiere() === $this) {
                $note->setMatiere(null);
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
            $emploiDuTemp->setMatiere($this);
        }

        return $this;
    }

    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getMatiere() === $this) {
                $emploiDuTemp->setMatiere(null);
            }
        }

        return $this;
    }

    public function getEnseignant(): ?Enseignant
    {
        return $this->enseignant;
    }

    public function setEnseignant(?Enseignant $enseignant): self
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    public function getVolumeHoraire(): ?string
    {
        return $this->volumeHoraire;
    }

    public function setVolumeHoraire(?string $volumeHoraire): self
    {
        $this->volumeHoraire = $volumeHoraire;

        return $this;
    }

    /**
     * @return Collection|EnseignantMatiere[]
     */
    public function getEnseignantMatieres(): Collection
    {
        return $this->enseignantMatieres;
    }

    public function addEnseignantMatiere(EnseignantMatiere $enseignantMatiere): self
    {
        if (!$this->enseignantMatieres->contains($enseignantMatiere)) {
            $this->enseignantMatieres[] = $enseignantMatiere;
            $enseignantMatiere->setMatiere($this);
        }

        return $this;
    }

    public function removeEnseignantMatiere(EnseignantMatiere $enseignantMatiere): self
    {
        if ($this->enseignantMatieres->removeElement($enseignantMatiere)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMatiere->getMatiere() === $this) {
                $enseignantMatiere->setMatiere(null);
            }
        }

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
            $fichePresenceEnseignant->setMatiere($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getMatiere() === $this) {
                $fichePresenceEnseignant->setMatiere(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function getTauxHoraire(): ?string
    {
        return $this->taux_horaire;
    }

    public function setTauxHoraire(?string $taux_horaire): self
    {
        $this->taux_horaire = $taux_horaire;

        return $this;
    }

    public function getSyllabus(): ?string
    {
        return $this->syllabus;
    }

    public function setSyllabus(?string $syllabus): self
    {
        $this->syllabus = $syllabus;

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
