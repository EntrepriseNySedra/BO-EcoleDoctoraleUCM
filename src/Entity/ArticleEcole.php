<?php

namespace App\Entity;

use App\Repository\ArticleEcoleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleEcoleRepository::class)
 */
class ArticleEcole
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $titre;


    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $detail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    
     /**
     * @ORM\OneToOne(targetEntity=TypeArticleEcole::class)
     * @ORM\JoinColumn(name="typeArticleEcole_id", referencedColumnName="id", nullable=true)
     */
    private $typeArticleEcole;

   
    public function getTypeArticleEcole(): ?TypeArticleEcole
    {
        return $this->typeArticleEcole;
    }

    public function setTypeArticleEcole(?TypeArticleEcole $typeArticleEcole): self
    {
        $this->typeArticleEcole = $typeArticleEcole;

        return $this;
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

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
