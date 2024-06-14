<?php

namespace App\Entity;

use App\Repository\NiveauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=NiveauRepository::class)
 */
class Niveau
{
    const L1_CODE = "L1";
    const L3_CODE = "L3";
    const M1_CODE = "M1";
    const M2_CODE = "M2";
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
     * @ORM\Column(type="string", length=45)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $systeme;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $diplome;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=UniteEnseignements::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $uniteEnseignements;

    /**
     * @ORM\OneToMany(targetEntity=Concours::class, mappedBy="niveau")
     */
    private $Concours;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursCandidature::class, mappedBy="niveau")
     */
    private $concoursCandidatures;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="niveau_id")
     */
    private $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMention::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $enseignantMentions;

    /**
     * @ORM\OneToMany(targetEntity=Semestre::class, mappedBy="niveau")
     */
    private $semestres;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="niveau")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $absences;

    /**
     * @ORM\OneToMany(targetEntity=DemandeDoc::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $demandeDocs;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=Parcours::class, mappedBy="niveau")
     */
    private $parcours;

    /**
     * @ORM\OneToMany(targetEntity=Ecolage::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $ecolages;

    /**
     * @ORM\OneToMany(targetEntity=BankCompte::class, mappedBy="niveau")
     */
    private $bankComptes;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->uniteEnseignements = new ArrayCollection();
        $this->Concours = new ArrayCollection();
        $this->concoursCandidatures = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->enseignantMentions = new ArrayCollection();
        $this->semestres = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->demandeDocs = new ArrayCollection();
        $this->parcours = new ArrayCollection();
        $this->ecolages = new ArrayCollection();
        $this->bankComptes = new ArrayCollection();
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

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getSysteme(): ?string
    {
        return $this->systeme;
    }

    public function setSysteme(?string $systeme): self
    {
        $this->systeme = $systeme;

        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(?string $diplome): self
    {
        $this->diplome = $diplome;

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
     * @return Collection|UniteEnseignements[]
     */
    public function getUniteEnseignements(): Collection
    {
        return $this->uniteEnseignements;
    }

    public function addUniteEnseignement(UniteEnseignements $uniteEnseignement): self
    {
        if (!$this->uniteEnseignements->contains($uniteEnseignement)) {
            $this->uniteEnseignements[] = $uniteEnseignement;
            $uniteEnseignement->setNiveau($this);
        }

        return $this;
    }

    public function removeUniteEnseignement(UniteEnseignements $uniteEnseignement): self
    {
        if ($this->uniteEnseignements->removeElement($uniteEnseignement)) {
            // set the owning side to null (unless already changed)
            if ($uniteEnseignement->getNiveau() === $this) {
                $uniteEnseignement->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Concours[]
     */
    public function getConcours(): Collection
    {
        return $this->Concours;
    }

    public function addCouncour(Concours $councour): self
    {
        if (!$this->Concours->contains($councour)) {
            $this->Concours[] = $councour;
            $councour->setNiveau($this);
        }

        return $this;
    }

    public function removeCouncour(Concours $councour): self
    {
        if ($this->Concours->removeElement($councour)) {
            // set the owning side to null (unless already changed)
            if ($councour->getNiveau() === $this) {
                $councour->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConcoursCandidature[]
     */
    public function getConcoursCandidatures(): Collection
    {
        return $this->concoursCandidatures;
    }

    public function addConcoursCandidature(ConcoursCandidature $concoursCandidature): self
    {
        if (!$this->concoursCandidatures->contains($concoursCandidature)) {
            $this->concoursCandidatures[] = $concoursCandidature;
            $concoursCandidature->setNiveau($this);
        }

        return $this;
    }

    public function removeConcoursCandidature(ConcoursCandidature $concoursCandidature): self
    {
        if ($this->concoursCandidatures->removeElement($concoursCandidature)) {
            // set the owning side to null (unless already changed)
            if ($concoursCandidature->getNiveau() === $this) {
                $concoursCandidature->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Etudiant[]
     */
    public function getEtudiants(): Collection
    {
        return $this->etudiants;
    }

    public function addEtudiant(Etudiant $etudiant): self
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants[] = $etudiant;
            $etudiant->setNiveau($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getNiveau() === $this) {
                $etudiant->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EnseignantMention[]
     */
    public function getEnseignantMentions(): Collection
    {
        return $this->enseignantMentions;
    }

    public function addEnseignantMention(EnseignantMention $enseignantMention): self
    {
        if (!$this->enseignantMentions->contains($enseignantMention)) {
            $this->enseignantMentions[] = $enseignantMention;
            $enseignantMention->setNiveau($this);
        }

        return $this;
    }

    public function removeEnseignantMention(EnseignantMention $enseignantMention): self
    {
        if ($this->enseignantMentions->removeElement($enseignantMention)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMention->getNiveau() === $this) {
                $enseignantMention->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Semestre[]
     */
    public function getSemestres(): Collection
    {
        return $this->semestres;
    }

    public function addSemestre(Semestre $semestre): self
    {
        if (!$this->semestres->contains($semestre)) {
            $this->semestres[] = $semestre;
            $semestre->setNiveau($this);
        }

        return $this;
    }

    public function removeSemestre(Semestre $semestre): self
    {
        if ($this->semestres->removeElement($semestre)) {
            // set the owning side to null (unless already changed)
            if ($semestre->getNiveau() === $this) {
                $semestre->setNiveau(null);
            }
        }

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
            $fichePresenceEnseignant->setNiveau($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getNiveau() === $this) {
                $fichePresenceEnseignant->setNiveau(null);
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
            $absence->setNiveau($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getNiveau() === $this) {
                $absence->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DemandeDoc[]
     */
    public function getDemandeDocs(): Collection
    {
        return $this->demandeDocs;
    }

    public function addDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if (!$this->demandeDocs->contains($demandeDoc)) {
            $this->demandeDocs[] = $demandeDoc;
            $demandeDoc->setNiveau($this);
        }

        return $this;
    }

    public function removeDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if ($this->demandeDocs->removeElement($demandeDoc)) {
            // set the owning side to null (unless already changed)
            if ($demandeDoc->getNiveau() === $this) {
                $demandeDoc->setNiveau(null);
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

    /**
     * @return Collection|Parcours[]
     */
    public function getParcours(): Collection
    {
        return $this->parcours;
    }

    public function addParcour(Parcours $parcour): self
    {
        if (!$this->parcours->contains($parcour)) {
            $this->parcours[] = $parcour;
            $parcour->setNiveau($this);
        }

        return $this;
    }

    public function removeParcour(Parcours $parcour): self
    {
        if ($this->parcours->removeElement($parcour)) {
            // set the owning side to null (unless already changed)
            if ($parcour->getNiveau() === $this) {
                $parcour->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ecolage[]
     */
    public function getEcolages(): Collection
    {
        return $this->ecolages;
    }

    public function addEcolage(Ecolage $ecolage): self
    {
        if (!$this->ecolages->contains($ecolage)) {
            $this->ecolages[] = $ecolage;
            $ecolage->setNiveau($this);
        }

        return $this;
    }

    public function removeEcolage(Ecolage $ecolage): self
    {
        if ($this->ecolages->removeElement($ecolage)) {
            // set the owning side to null (unless already changed)
            if ($ecolage->getNiveau() === $this) {
                $ecolage->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BankCompte>
     */
    public function getBankComptes(): Collection
    {
        return $this->bankComptes;
    }

    public function addBankCompte(BankCompte $bankCompte): self
    {
        if (!$this->bankComptes->contains($bankCompte)) {
            $this->bankComptes[] = $bankCompte;
            $bankCompte->setNiveau($this);
        }

        return $this;
    }

    public function removeBankCompte(BankCompte $bankCompte): self
    {
        if ($this->bankComptes->removeElement($bankCompte)) {
            // set the owning side to null (unless already changed)
            if ($bankCompte->getNiveau() === $this) {
                $bankCompte->setNiveau(null);
            }
        }

        return $this;
    }
}
