<?php

namespace App\Entity;

use App\Repository\ParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ParcoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Parcours
{
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
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $parcourscool;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="parcours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=UniteEnseignements::class, mappedBy="parcours")
     */
    private $uniteEnseignements;

    /**
     * @ORM\OneToMany(targetEntity=Etudiant::class, mappedBy="parcours")
     */
    private $etudiants;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="parcours")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="parcours")
     */
    private $absences;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="parcours")
     */
    private $emploiDuTemps;

    /**
     * @ORM\OneToMany(targetEntity=DemandeDoc::class, mappedBy="parcours")
     */
    private $demandeDocs;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursCandidature::class, mappedBy="parcours")
     */
    private $concoursCandidatures;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="parcours")
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMention::class, mappedBy="parcours")
     */
    private $enseignantMentions;

    /**
     * @ORM\OneToMany(targetEntity=Ecolage::class, mappedBy="parcours")
     */
    private $ecolages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diminutif;

    /**
     * @ORM\OneToMany(targetEntity=BankCompte::class, mappedBy="parcours")
     */
    private $bankComptes;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->uniteEnseignements = new ArrayCollection();
        $this->etudiants = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
        $this->demandeDocs = new ArrayCollection();
        $this->concoursCandidatures = new ArrayCollection();
        $this->enseignantMentions = new ArrayCollection();
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

    public function getParcourscool(): ?string
    {
        return $this->parcourscool;
    }

    public function setParcourscool(?string $parcourscool): self
    {
        $this->parcourscool = $parcourscool;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

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
            $uniteEnseignement->setParcours($this);
        }

        return $this;
    }

    public function removeUniteEnseignement(UniteEnseignements $uniteEnseignement): self
    {
        if ($this->uniteEnseignements->removeElement($uniteEnseignement)) {
            // set the owning side to null (unless already changed)
            if ($uniteEnseignement->getParcours() === $this) {
                $uniteEnseignement->setParcours(null);
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
            $etudiant->setParcours($this);
        }

        return $this;
    }

    public function removeEtudiant(Etudiant $etudiant): self
    {
        if ($this->etudiants->removeElement($etudiant)) {
            // set the owning side to null (unless already changed)
            if ($etudiant->getParcours() === $this) {
                $etudiant->setParcours(null);
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
            $fichePresenceEnseignant->setParcours($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getParcours() === $this) {
                $fichePresenceEnseignant->setParcours(null);
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
            $absence->setParcours($this);
        }

        return $this;
    }

    public function removeAbsence(Absences $absence): self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getParcours() === $this) {
                $absence->setParcours(null);
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
            $emploiDuTemp->setParcours($this);
        }

        return $this;
    }

    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getParcours() === $this) {
                $emploiDuTemp->setParcours(null);
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
            $demandeDoc->setParcours($this);
        }

        return $this;
    }

    public function removeDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if ($this->demandeDocs->removeElement($demandeDoc)) {
            // set the owning side to null (unless already changed)
            if ($demandeDoc->getParcours() === $this) {
                $demandeDoc->setParcours(null);
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
            $concoursCandidature->setParcours($this);
        }

        return $this;
    }

    public function removeConcoursCandidature(ConcoursCandidature $concoursCandidature): self
    {
        if ($this->concoursCandidatures->removeElement($concoursCandidature)) {
            // set the owning side to null (unless already changed)
            if ($concoursCandidature->getParcours() === $this) {
                $concoursCandidature->setParcours(null);
            }
        }

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
            $enseignantMention->setParcours($this);
        }

        return $this;
    }

    public function removeEnseignantMention(EnseignantMention $enseignantMention): self
    {
        if ($this->enseignantMentions->removeElement($enseignantMention)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMention->getParcours() === $this) {
                $enseignantMention->setParcours(null);
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
            $ecolage->setParcours($this);
        }

        return $this;
    }

    public function removeEcolage(Ecolage $ecolage): self
    {
        if ($this->ecolages->removeElement($ecolage)) {
            // set the owning side to null (unless already changed)
            if ($ecolage->getParcours() === $this) {
                $ecolage->setParcours(null);
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
            $bankCompte->setParcours($this);
        }

        return $this;
    }

    public function removeBankCompte(BankCompte $bankCompte): self
    {
        if ($this->bankComptes->removeElement($bankCompte)) {
            // set the owning side to null (unless already changed)
            if ($bankCompte->getParcours() === $this) {
                $bankCompte->setParcours(null);
            }
        }

        return $this;
    }
}
