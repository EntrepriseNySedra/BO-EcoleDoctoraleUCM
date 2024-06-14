<?php

namespace App\Entity;

use App\Repository\SubventionRechercheRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubventionRechercheRepository::class)
 */
class SubventionRecherche
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sourceFinancement;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $exemple;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $critere;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $procedureCandidature;

    /**
     * @ORM\Column(type="text")
     */
    private $avantages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;


    /**
     * @ORM\OneToOne(targetEntity=ProjetRecherche::class)
     * @ORM\JoinColumn(name="projetRecherche_id", referencedColumnName="id", nullable=true)
     */
    private $projetRecherche;

   
    public function getProjetRecherche(): ?ProjetRecherche
    {
        return $this->projetRecherche;
    }

    public function setProjetRecherche(?ProjetRecherche $projetRecherche): self
    {
        $this->projetRecherche = $projetRecherche;

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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSourceFinancement(): ?string
    {
        return $this->sourceFinancement;
    }

    public function setSourceFinancement(?string $sourceFinancement): self
    {
        $this->sourceFinancement = $sourceFinancement;

        return $this;
    }

    public function getExemple(): ?string
    {
        return $this->exemple;
    }

    public function setExemple(?string $exemple): self
    {
        $this->exemple = $exemple;

        return $this;
    }

    public function getCritere(): ?string
    {
        return $this->critere;
    }

    public function setCritere(?string $critere): self
    {
        $this->critere = $critere;

        return $this;
    }

    public function getProcedureCandidature(): ?string
    {
        return $this->procedureCandidature;
    }

    public function setProcedureCandidature(?string $procedureCandidature): self
    {
        $this->procedureCandidature = $procedureCandidature;

        return $this;
    }

    public function getAvantages(): ?string
    {
        return $this->avantages;
    }

    public function setAvantages(string $avantages): self
    {
        $this->avantages = $avantages;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
