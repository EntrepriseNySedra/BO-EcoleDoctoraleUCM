<?php

namespace App\Entity;

use App\Repository\MentionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=MentionRepository::class)
 */
class Mention
{
    const DIM_SCIENCE_PO = "SPO";
    const OLD_ECOGE_PREFIX = "ECG";
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $objectif;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $dmio;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="mentions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $departement;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Secteur::class, mappedBy="mention", orphanRemoval=true)
     */
    private $secteurs;

    /**
     * @ORM\OneToMany(targetEntity=UniteEnseignements::class, mappedBy="mention", orphanRemoval=true)
     */
    private $uniteEnseignements;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $admission;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $diplomes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $debouches;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $path;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursMatiere::class, mappedBy="mention")
     */
    private $concoursMatieres;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursCandidature::class, mappedBy="mention")
     */
    private $concoursCandidatures;

    /**
     * @ORM\OneToMany(targetEntity=Parcours::class, mappedBy="mention")
     */
    private $parcours;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="mention_id")
     */
    private $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMention::class, mappedBy="mention", orphanRemoval=true)
     */
    private $enseignantMentions;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="mention")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="mention")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="mention")
     */
    private $absences;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="mention", orphanRemoval=true)
     */
    private $emploiDuTemps;

    /**
     * @ORM\OneToMany(targetEntity=DemandeDoc::class, mappedBy="mention", orphanRemoval=true)
     */
    private $demandeDocs;

    /**
     * @ORM\OneToMany(targetEntity=TypePrestationMention::class, mappedBy="mention", orphanRemoval=true)
     */
    private $typePrestationMentions;

    /**
     * @ORM\OneToMany(targetEntity=Ecolage::class, mappedBy="mention", orphanRemoval=true)
     */
    private $ecolages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diminutif;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numCompteGenerale;

    /**
     * @ORM\OneToMany(targetEntity=BankCompte::class, mappedBy="mention", orphanRemoval=true)
     */
    private $bankComptes;

    /**
     * @ORM\OneToMany(targetEntity=Prestation::class, mappedBy="mention")
     */
    private $prestations;
    
    


    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->secteurs = new ArrayCollection();
        $this->uniteEnseignements = new ArrayCollection();
        $this->concoursMatieres = new ArrayCollection();
        $this->concoursCandidatures = new ArrayCollection();
        $this->parcours = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->niveau = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
        $this->demandeDocs = new ArrayCollection();
        $this->typePrestationMentions = new ArrayCollection();
        $this->ecolages = new ArrayCollection();
        $this->bankComptes = new ArrayCollection();
        $this->prestations = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(?string $objectif): self
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getDmio(): ?string
    {
        return $this->dmio;
    }

    public function setDmio(?string $dmio): self
    {
        $this->dmio = $dmio;

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

    public function getAdmission(): ?string
    {
        return $this->admission;
    }

    public function setAdmission(?string $admission): self
    {
        $this->admission = $admission;

        return $this;
    }

    public function getDiplomes(): ?string
    {
        return $this->diplomes;
    }

    public function setDiplomes(?string $diplomes): self
    {
        $this->diplomes = $diplomes;

        return $this;
    }

    public function getDebouches(): ?string
    {
        return $this->debouches;
    }

    public function setDebouches(?string $debouches): self
    {
        $this->debouches = $debouches;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Collection|Secteur[]
     */
    public function getSecteurs(): Collection
    {
        return $this->secteurs;
    }

    public function addSecteur(Secteur $secteur): self
    {
        if (!$this->secteurs->contains($secteur)) {
            $this->secteurs[] = $secteur;
            $secteur->setMention($this);
        }

        return $this;
    }

    public function removeSecteur(Secteur $secteur): self
    {
        if ($this->secteurs->removeElement($secteur)) {
            // set the owning side to null (unless already changed)
            if ($secteur->getMention() === $this) {
                $secteur->setMention(null);
            }
        }

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
            $uniteEnseignement->setMention($this);
        }

        return $this;
    }

    public function removeUniteEnseignement(UniteEnseignements $uniteEnseignement): self
    {
        if ($this->uniteEnseignements->removeElement($uniteEnseignement)) {
            // set the owning side to null (unless already changed)
            if ($uniteEnseignement->getMention() === $this) {
                $uniteEnseignement->setMention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConcoursMatiere[]
     */
    public function getConcoursMatieres(): Collection
    {
        return $this->concoursMatieres;
    }

    public function addConcoursMatiere(ConcoursMatiere $concoursMatiere): self
    {
        if (!$this->concoursMatieres->contains($concoursMatiere)) {
            $this->concoursMatieres[] = $concoursMatiere;
            $concoursMatiere->setMention($this);
        }

        return $this;
    }

    public function removeConcoursMatiere(ConcoursMatiere $concoursMatiere): self
    {
        if ($this->concoursMatieres->removeElement($concoursMatiere)) {
            // set the owning side to null (unless already changed)
            if ($concoursMatiere->getMention() === $this) {
                $concoursMatiere->setMention(null);
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
            $concoursCandidature->setMention($this);
        }
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
            $parcour->setMentionId($this);
        }

        return $this;
    }

    public function removeConcoursCandidature(ConcoursCandidature $concoursCandidature): self
    {
        if ($this->concoursCandidatures->removeElement($concoursCandidature)) {
            // set the owning side to null (unless already changed)
            if ($concoursCandidature->getMention() === $this) {
                $concoursCandidature->setMention(null);
            }
        }
    }
    
    public function removeParcour(Parcours $parcour): self
    {
        if ($this->parcours->removeElement($parcour)) {
            // set the owning side to null (unless already changed)
            if ($parcour->getMentionId() === $this) {
                $parcour->setMentionId(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

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
            $etudiant->setMentionId($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getMention() === $this) {
                $etudiant->setMention(null);
            }
        }

        return $this;
    }

   

    public function addNiveau(EnseignantMention $niveau): self
    {
        if (!$this->niveau->contains($niveau)) {
            $this->niveau[] = $niveau;
            $niveau->setMention($this);
        }

        return $this;
    }

    public function removeNiveau(EnseignantMention $niveau): self
    {
        if ($this->niveau->removeElement($niveau)) {
            // set the owning side to null (unless already changed)
            if ($niveau->getMention() === $this) {
                $niveau->setMention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setMention($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getMention() === $this) {
                $user->setMention(null);
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
            $fichePresenceEnseignant->setMention($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getMention() === $this) {
                $fichePresenceEnseignant->setMention(null);
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
            $absence->setMention($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getMention() === $this) {
                $absence->setMention(null);
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
            $emploiDuTemp->setMention($this);
        }

        return $this;
    }

    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getMention() === $this) {
                $emploiDuTemp->setMention(null);
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
            $demandeDoc->setMention($this);
        }

        return $this;
    }

    public function removeDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if ($this->demandeDocs->removeElement($demandeDoc)) {
            // set the owning side to null (unless already changed)
            if ($demandeDoc->getMention() === $this) {
                $demandeDoc->setMention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TypePrestationMention[]
     */
    public function getTypePrestationMentions(): Collection
    {
        return $this->typePrestationMentions;
    }

    public function addTypePrestationMention(TypePrestationMention $typePrestationMention): self
    {
        if (!$this->typePrestationMentions->contains($typePrestationMention)) {
            $this->typePrestationMentions[] = $typePrestationMention;
            $typePrestationMention->setMention($this);
        }

        return $this;
    }

    public function removeTypePrestationMention(TypePrestationMention $typePrestationMention): self
    {
        if ($this->typePrestationMentions->removeElement($typePrestationMention)) {
            // set the owning side to null (unless already changed)
            if ($typePrestationMention->getMention() === $this) {
                $typePrestationMention->setMention(null);
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
            $ecolage->setMention($this);
        }

        return $this;
    }

    public function removeEcolage(Ecolage $ecolage): self
    {
        if ($this->ecolages->removeElement($ecolage)) {
            // set the owning side to null (unless already changed)
            if ($ecolage->getMention() === $this) {
                $ecolage->setMention(null);
            }
        }

        return $this;
    }

    public function getDiminutif(): ?string
    {
        return $this->diminutif;
    }

    public function setDiminutif(?string $diminutif): self
    {
        $this->diminutif = $diminutif;

        return $this;
    }

    public function getNumCompteGenerale(): ?string
    {
        return $this->numCompteGenerale;
    }

    public function setNumCompteGenerale(?string $numCompteGenerale): self
    {
        $this->numCompteGenerale = $numCompteGenerale;

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
            $bankCompte->setMention($this);
        }

        return $this;
    }

    public function removeBankCompte(BankCompte $bankCompte): self
    {
        if ($this->bankComptes->removeElement($bankCompte)) {
            // set the owning side to null (unless already changed)
            if ($bankCompte->getMention() === $this) {
                $bankCompte->setMention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Prestation>
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
            $prestation->setMention($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getMention() === $this) {
                $prestation->setMention(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNom(); // Remplacez par la propriété que vous souhaitez afficher
    }
}
