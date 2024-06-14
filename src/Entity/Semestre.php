<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=SemestreRepository::class)
 */
class Semestre
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
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=UniteEnseignements::class, mappedBy="semestre", orphanRemoval=true)
     */
    private $uniteEnseignements;

    /**
     * @ORM\OneToMany(targetEntity=Notes::class, mappedBy="semestre")
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="semestres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity=EmploiDuTemps::class, mappedBy="semestre", orphanRemoval=true)
     */
    private $emploiDuTemps;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ecolage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $credit;

    /**
     * @ORM\OneToMany(targetEntity=Ecolage::class, mappedBy="semestre", orphanRemoval=true)
     */
    private $ecolages;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->uniteEnseignements = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->emploiDuTemps = new ArrayCollection();
        $this->ecolages = new ArrayCollection();
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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
            $uniteEnseignement->setSemestre($this);
        }

        return $this;
    }

    public function removeUniteEnseignement(UniteEnseignements $uniteEnseignement): self
    {
        if ($this->uniteEnseignements->removeElement($uniteEnseignement)) {
            // set the owning side to null (unless already changed)
            if ($uniteEnseignement->getSemestre() === $this) {
                $uniteEnseignement->setSemestre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notes[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setSemestre($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getSemestre() === $this) {
                $note->setSemestre(null);
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
            $emploiDuTemp->setSemestre($this);
        }

        return $this;
    }

    public function removeEmploiDuTemp(EmploiDuTemps $emploiDuTemp): self
    {
        if ($this->emploiDuTemps->removeElement($emploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploiDuTemp->getSemestre() === $this) {
                $emploiDuTemp->setSemestre(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
    }

    public function getEcolage(): ?float
    {
        return $this->ecolage;
    }

    public function setEcolage(?float $ecolage): self
    {
        $this->ecolage = $ecolage;

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
            $ecolage->setSemestre($this);
        }

        return $this;
    }

    public function removeEcolage(Ecolage $ecolage): self
    {
        if ($this->ecolages->removeElement($ecolage)) {
            // set the owning side to null (unless already changed)
            if ($ecolage->getSemestre() === $this) {
                $ecolage->setSemestre(null);
            }
        }

        return $this;
    }
}
