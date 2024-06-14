<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Article
{
    //List of resource articable
    static $ressourceTypeList = [
        'Rubrique'      => 'RUBRIQUE',
        'Département'   => 'DEPARTEMENT',
        'Mention'       => 'MENTION',
        'Niveau'        => 'NIVEAU',
        //'Semestre'      => 'SEMESTRE',
    ];

    //List of resource location
    static $emplacementTypeList = [
        'EN HAUT'       => 'CONTENT-UP',
        'EN BAS'        => 'CONTENT-DOWN',
        'À DROITE'      => 'CONTENT-RIGHT',
        'DE GAUCHE'     => 'CONTENT-LEFT',
        'AU MILIEU'     => 'CONTENT-BRICK-CENTER',
        'SATELLITE'     => 'CONTENT-BRICK-SATELLITE',
        'SOUS RUBRQIUE' => 'SUB-RUBRIQUE'
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content_header;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content_footer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content_left;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content_right;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="article")
     */
    private $documents;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('RUBRIQUE', 'DEPARTEMENT', 'MENTION', 'NIVEAU', 'SEMESTRE')", nullable=true)
     */
    private $ressourceType;

    /**
     * @ORM\Column(type="string", length=45)
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
     * @ORM\Column(type="string", length=45, columnDefinition="ENUM('MILIEU', 'GAUCHE', 'DROITE', 'HAUT', 'BAS')",nullable=true)
     */
    private $emplacement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

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

    public function setMotCle(string $motCle): self
    {
        $this->motCle = $motCle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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
            $document->setArticle($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getArticle() === $this) {
                $document->setArticle(null);
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
     * @ORM\PostUpdate()
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

    public function setResourceUuid(string $resourceUuid): self
    {
        $this->resourceUuid = $resourceUuid;

        return $this;
    }

    public function getContentHeader(): ?string
    {
        return $this->content_header;
    }

    public function setContentHeader(string $content_header): self
    {
        $this->content_header = $content_header;

        return $this;
    }

    public function getContentFooter(): ?string
    {
        return $this->content_footer;
    }

    public function setContentFooter(string $content_footer): self
    {
        $this->content_footer = $content_footer;

        return $this;
    }

    public function getContentLeft(): ?string
    {
        return $this->content_left;
    }

    public function setContentLeft(string $content_left): self
    {
        $this->content_left = $content_left;

        return $this;
    }

    public function getContentRight(): ?string
    {
        return $this->content_right;
    }

    public function setContentRight(string $content_right): self
    {
        $this->content_right = $content_right;

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

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): self
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

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
