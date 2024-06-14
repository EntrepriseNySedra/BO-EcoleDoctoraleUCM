<?php

namespace App\Entity;

use App\Repository\CoursMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=CoursMediaRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CoursMedia
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
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

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
     * @ORM\ManyToOne(targetEntity=Cours::class, inversedBy="coursMedia")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cours;

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return \App\Entity\CoursMedia
     */
    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return \App\Entity\CoursMedia
     */
    public function setName($name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return \App\Entity\CoursMedia
     */
    public function setPath(string $path) : self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return \App\Entity\CoursMedia
     */
    public function setCreatedAt(\DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return \App\Entity\CoursMedia
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt() : ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeInterface|null $deletedAt
     *
     * @return \App\Entity\CoursMedia
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \App\Entity\Cours|null
     */
    public function getCours() : ?Cours
    {
        return $this->cours;
    }

    /**
     * @param \App\Entity\Cours|null $cours
     *
     * @return \App\Entity\CoursMedia
     */
    public function setCours(?Cours $cours) : self
    {
        $this->cours = $cours;

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

    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile */
    private $file;

    // Temporary store the file name
    private $tempFilename;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // Replacing a file ? Check if we already have a file for this entity
        if (null !== $this->type) {
            // Save file extension so we can remove it later
            $this->tempFilename = $this->type;

            // Reset values
            $this->type = null;
            $this->name = null;
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {

        // If no file is set, do nothing
        if (null === $this->file) {
            return;
        }

        // The file name is the entity's ID
        // We also need to store the file extension
        $this->type = $this->file->guessExtension();

        // And we keep the original name
        $this->path = $this->file->getClientOriginalName();

        // path
        $this->name = str_replace('.' . $this->type, '', $this->path);
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // If no file is set, do nothing
        if (null === $this->file) {
            return;
        }

        // A file is present, remove it
        if (null !== $this->tempFilename) {
            //$oldFile = $this->getUploadRootDir() . '/' . $this->name . '.' . $this->tempFilename;
            $oldFile = $this->getUploadRootDir() . '/' . $this->path ;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move the file to the upload folder
        $this->file->move(
            $this->getUploadRootDir(),
            //$this->name . '.' . $this->type
            $this->path
        );
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // Save the name of the file we would want to remove
        //$this->tempFilename = $this->getUploadRootDir() . '/' . $this->name . '.' . $this->type;
        $this->tempFilename = $this->getUploadRootDir() . '/' . $this->path;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // PostRemove => We no longer have the entity's ID => Use the name we saved
        if (file_exists($this->tempFilename)) {
            // Remove file
            unlink($this->tempFilename);
        }
    }

    public function getUploadDir()
    {
        // Upload directory
        return 'public/uploads/cours/';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        // Image location (PHP)
        return ($this->cours) ? __DIR__ . '/../../' . $this->getUploadDir() . '/' . $this->cours->getId() : null;
    }
}
