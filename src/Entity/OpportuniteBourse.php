<?php

namespace App\Entity;

use App\Repository\OpportuniteBourseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OpportuniteBourseRepository::class)
 */
class OpportuniteBourse
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $critere;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $montant;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $delais;

    /**
     * @ORM\OneToOne(targetEntity=BourseDistinction::class)
     * @ORM\JoinColumn(name="bourse_id", referencedColumnName="id", nullable=true)
     */
    private $bourse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;


    public function getBourse(): ?BourseDistinction
    {
        return $this->bourse;
    }

    public function setBourse(?BourseDistinction $bourse): self
    {
        $this->bourse = $bourse;

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

    public function getCritere(): ?string
    {
        return $this->critere;
    }

    public function setCritere(?string $critere): self
    {
        $this->critere = $critere;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDelais(): ?\DateTimeInterface
    {
        return $this->delais;
    }

    public function setDelais(?\DateTimeInterface $delais): self
    {
        $this->delais = $delais;

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
