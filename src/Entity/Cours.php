<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Cours
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="cours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $matiere;

    /**
     * @ORM\OneToMany(targetEntity=CoursMedia::class, mappedBy="cours", cascade={"persist"}, orphanRemoval=true)
     */
    protected $coursMedia;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbSequence;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\OneToMany(targetEntity=CoursSection::class, mappedBy="cours", orphanRemoval=true)
     */
    private $section;

    /**
     * @ORM\OneToMany(targetEntity=EtudiantDocument::class, mappedBy="cours", orphanRemoval=true)
     */
    private $etudiantDocuments;

    /**
     * Cours constructor.
     */
    public function __construct()
    {
        $this->coursMedia = new ArrayCollection();
        $this->section = new ArrayCollection();
        $this->etudiantDocuments = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     *
     * @return \App\Entity\Cours
     */
    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return \App\Entity\Cours
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return \App\Entity\Cours
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeInterface|null $deletedAt
     *
     * @return \App\Entity\Cours
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \App\Entity\Matiere|null
     */
    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    /**
     * @param \App\Entity\Matiere|null $matiere
     *
     * @return \App\Entity\Cours
     */
    public function setMatiere(?Matiere $matiere): self
    {
        $this->matiere = $matiere;

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

    /**
     * @return Collection|CoursMedia[]
     */
    public function getCoursMedia(): Collection
    {
        return $this->coursMedia;
    }

    /**
     * @param \App\Entity\CoursMedia $coursMedium
     *
     * @return \App\Entity\Cours
     */
    public function addCoursMedium(CoursMedia $coursMedium): self
    {
        if (!$this->coursMedia->contains($coursMedium)) {
            $this->coursMedia[] = $coursMedium;
            $coursMedium->setCours($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\CoursMedia $coursMedium
     *
     * @return \App\Entity\Cours
     */
    public function removeCoursMedium(CoursMedia $coursMedium): self
    {
        if ($this->coursMedia->removeElement($coursMedium)) {
            // set the owning side to null (unless already changed)
            if ($coursMedium->getCours() === $this) {
                $coursMedium->setCours(null);
            }
        }

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

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire): self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNbSequence()
    {
        return $this->nbSequence;
    }

    /**
     * @param mixed $nbSequence
     *
     * @return Cours
     */
    public function setNbSequence($nbSequence)
    {
        $this->nbSequence = $nbSequence;

        return $this;
    }

    /**
     * @return Collection<int, CoursSection>
     */
    public function getSection(): Collection
    {
        return $this->section;
    }

    public function addSection(CoursSection $section): self
    {
        if (!$this->section->contains($section)) {
            $this->section[] = $section;
            $section->setCours($this);
        }

        return $this;
    }

    public function removeSection(CoursSection $section): self
    {
        if ($this->section->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getCours() === $this) {
                $section->setCours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EtudiantDocument>
     */
    public function getEtudiantDocuments(): Collection
    {
        return $this->etudiantDocuments;
    }

    public function addEtudiantDocument(EtudiantDocument $etudiantDocument): self
    {
        if (!$this->etudiantDocuments->contains($etudiantDocument)) {
            $this->etudiantDocuments[] = $etudiantDocument;
            $etudiantDocument->setCours($this);
        }

        return $this;
    }

    public function removeEtudiantDocument(EtudiantDocument $etudiantDocument): self
    {
        if ($this->etudiantDocuments->removeElement($etudiantDocument)) {
            // set the owning side to null (unless already changed)
            if ($etudiantDocument->getCours() === $this) {
                $etudiantDocument->setCours(null);
            }
        }

        return $this;
    }
}