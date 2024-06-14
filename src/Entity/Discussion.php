<?php

namespace App\Entity;

use App\Repository\DiscussionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscussionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Discussion
{
    const STATUS_CREATED = 1;
    const STATUS_VALIDATED = 2;
    const STATUS_REJECTED = -1;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="discussions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=DiscussionCommentaire::class, mappedBy="auteur", orphanRemoval=true)
     */
    private $discussionCommentaires;

    public function __construct()
    {
        $this->discussionCommentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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
        if (!$this->created_at) {
            $this->created_at = new \DateTime();
        }
    }

    /**
     * @throws \Exception
     */
    private function setUpdatedAtValue()
    {
        $this->updated_at = new \DateTime();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, DiscussionCommentaire>
     */
    public function getDiscussionCommentaires(): Collection
    {
        return $this->discussionCommentaires;
    }

    public function addDiscussionCommentaire(DiscussionCommentaire $discussionCommentaire): self
    {
        if (!$this->discussionCommentaires->contains($discussionCommentaire)) {
            $this->discussionCommentaires[] = $discussionCommentaire;
            $discussionCommentaire->setAuteur($this);
        }

        return $this;
    }

    public function removeDiscussionCommentaire(DiscussionCommentaire $discussionCommentaire): self
    {
        if ($this->discussionCommentaires->removeElement($discussionCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($discussionCommentaire->getAuteur() === $this) {
                $discussionCommentaire->setAuteur(null);
            }
        }

        return $this;
    }
}
