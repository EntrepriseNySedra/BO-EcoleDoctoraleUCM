<?php

namespace App\Entity;

use App\Repository\ConcoursMatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConcoursMatiereRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConcoursMatiere
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
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Concours::class, inversedBy="concoursMatieres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $concours;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="concoursMatieres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursNotes::class, mappedBy="concoursMatiere")
     */
    private $concoursNotes;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursEmploiDuTemps::class, mappedBy="concoursMatiere")
     */
    private $concoursEmploiDuTemps;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class)
     */
    private $parcours;

    public function __construct()
    {
        $this->concoursNotes = new ArrayCollection();
        $this->concoursEmploiDuTemps = new ArrayCollection();
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
     * @return \App\Entity\ConcoursMatiere
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
     * @return \App\Entity\ConcoursMatiere
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
     * @param \DateTimeInterface $updatedAt
     *
     * @return \App\Entity\ConcoursMatiere
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
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
     * @return \App\Entity\ConcoursMatiere
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \App\Entity\Concours|null
     */
    public function getConcours(): ?Concours
    {
        return $this->concours;
    }

    /**
     * @param \App\Entity\Concours|null $concours
     *
     * @return \App\Entity\ConcoursMatiere
     */
    public function setConcours(?Concours $concours): self
    {
        $this->concours = $concours;

        return $this;
    }

    /**
     * @return \App\Entity\Mention|null
     */
    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    /**
     * @param \App\Entity\Mention|null $mention
     *
     * @return \App\Entity\ConcoursMatiere
     */
    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

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
     * @return Collection|ConcoursNotes[]
     */
    public function getConcoursNotes(): Collection
    {
        return $this->concoursNotes;
    }

    public function addConcoursNote(ConcoursNotes $concoursNote): self
    {
        if (!$this->concoursNotes->contains($concoursNote)) {
            $this->concoursNotes[] = $concoursNote;
            $concoursNote->setConcoursMatiere($this);
        }

        return $this;
    }

    public function removeConcoursNote(ConcoursNotes $concoursNote): self
    {
        if ($this->concoursNotes->removeElement($concoursNote)) {
            // set the owning side to null (unless already changed)
            if ($concoursNote->getConcoursMatiere() === $this) {
                $concoursNote->setConcoursMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConcoursEmploiDuTemps[]
     */
    public function getConcoursEmploiDuTemps(): Collection
    {
        return $this->concoursEmploiDuTemps;
    }

    public function addConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if (!$this->concoursEmploiDuTemps->contains($concoursEmploiDuTemp)) {
            $this->concoursEmploiDuTemps[] = $concoursEmploiDuTemp;
            $concoursEmploiDuTemp->setConcoursMatiere($this);
        }

        return $this;
    }

    public function removeConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if ($this->concoursEmploiDuTemps->removeElement($concoursEmploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($concoursEmploiDuTemp->getConcoursMatiere() === $this) {
                $concoursEmploiDuTemp->setConcoursMatiere(null);
            }
        }

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
}
