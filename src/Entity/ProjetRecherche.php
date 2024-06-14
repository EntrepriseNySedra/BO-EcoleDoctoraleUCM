<?php

namespace App\Entity;

use App\Repository\ProjetRechercheRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetRechercheRepository::class)
 */
class ProjetRecherche
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
     * @ORM\Column(type="string", length=255)
     */
    private $contribution;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $methodologie;

    /**
     * @ORM\Column(type="text")
     */
    private $objectif;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

     /**
     * @ORM\OneToOne(targetEntity=DomaineRecherche::class)
     * @ORM\JoinColumn(name="domainerecherche_id", referencedColumnName="id", nullable=true)
     */
    private $domaineRecherche;

   
    public function getDomaineRecherche(): ?DomaineRecherche
    {
        return $this->domaineRecherche;
    }

    public function setDomaineRecherche(?DomaineRecherche $domaineRecherche): self
    {
        $this->domaineRecherche = $domaineRecherche;

        return $this;
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

    public function getContribution(): ?string
    {
        return $this->contribution;
    }

    public function setContribution(string $contribution): self
    {
        $this->contribution = $contribution;

        return $this;
    }

    public function getMethodologie(): ?string
    {
        return $this->methodologie;
    }

    public function setMethodologie(string $methodologie): self
    {
        $this->methodologie = $methodologie;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $this->objectif = $objectif;

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
