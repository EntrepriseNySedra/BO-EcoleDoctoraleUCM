<?php

namespace App\Entity;

use App\Repository\ConcoursNotesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConcoursNotesRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConcoursNotes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ConcoursCandidature::class, inversedBy="concoursNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $concoursCandidature;

    /**
     * @ORM\ManyToOne(targetEntity=ConcoursMatiere::class, inversedBy="concoursNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $concoursMatiere;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity=Concours::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $concours;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcoursCandidature(): ?ConcoursCandidature
    {
        return $this->concoursCandidature;
    }

    public function setConcoursCandidature(?ConcoursCandidature $concoursCandidature): self
    {
        $this->concoursCandidature = $concoursCandidature;

        return $this;
    }

    public function getConcoursMatiere(): ?ConcoursMatiere
    {
        return $this->concoursMatiere;
    }

    public function setConcoursMatiere(?ConcoursMatiere $concoursMatiere): self
    {
        $this->concoursMatiere = $concoursMatiere;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getConcours(): ?Concours
    {
        return $this->concours;
    }

    public function setConcours(?Concours $concours): self
    {
        $this->concours = $concours;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

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
}
