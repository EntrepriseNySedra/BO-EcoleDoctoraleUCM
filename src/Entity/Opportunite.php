<?php

namespace App\Entity;

use Ramsey\Uuid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;

use App\Repository\OpportuniteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OpportuniteRepository::class)
 */
class Opportunite
{
    static $ressourceTypeList = [
        'Rubrique'      => 'RUBRIQUE',
        'Département'   => 'DEPARTEMENT',
        'Mention'       => 'MENTION',
        'Niveau'        => 'NIVEAU',
        //'Semestre'      => 'SEMESTRE',
    ];
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
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motCle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="opportunite")
     */
    private $documents;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('RUBRIQUE', 'DEPARTEMENT', 'MENTION', 'NIVEAU', 'SEMESTRE')", nullable=true)
     */
    private $ressourceType;

    /**
     * @ORM\Column(type="string", length=45 ,nullable=true)
     */
    private $resourceUuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMotCle(): ?string
    {
        return $this->motCle;
    }

    public function setMotCle(?string $motCle): self
    {
        $this->motCle = $motCle;

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

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setActualite($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getActualite() === $this) {
                $document->setActualite(null);
            }
        }

        return $this;
    }

    public function getRessourcetype(): ?string
    {
        return $this->ressourceType;
    }

    public function setRessourcetype(?string $ressourceType): self
    {
        $this->ressourceType = $ressourceType;

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
     * @return false|int|string
     */
    public function getFormatedStatus()
    {
        return array_search($this->ressourceType, self::$ressourceTypeList);
    }

    public function getResourceUuid(): ?string
    {
        return $this->resourceUuid;
    }

    public function setResourceUuid(?string $resourceUuid): self
    {
        $this->resourceUuid = $resourceUuid;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
