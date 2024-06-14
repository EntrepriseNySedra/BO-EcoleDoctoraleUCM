<?php

namespace App\Entity;

use App\Repository\ConcoursCentreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConcoursCentreRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConcoursCentre
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursCandidature::class, mappedBy="centre")
     */
    private $concoursCandidatures;

    public function __construct()
    {
        $this->concoursCandidatures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;

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

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
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
        $this->created_at = !$this->created_at ? new \DateTime() : $this->created_at;
    }

    /**
     * @throws \Exception
     */
    private function setUpdatedAtValue()
    {
        $this->updated_at = !$this->updated_at ? new \DateTime() : $this->updated_at;
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
            $concoursCandidature->setCentre($this);
        }

        return $this;
    }

    public function removeConcoursCandidature(ConcoursCandidature $concoursCandidature): self
    {
        if ($this->concoursCandidatures->removeElement($concoursCandidature)) {
            // set the owning side to null (unless already changed)
            if ($concoursCandidature->getCentre() === $this) {
                $concoursCandidature->setCentre(null);
            }
        }

        return $this;
    }



}
