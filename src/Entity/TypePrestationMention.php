<?php

namespace App\Entity;

use App\Repository\TypePrestationMentionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypePrestationMentionRepository::class)
 */
class TypePrestationMention
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="typePrestationMentions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=TypePrestation::class, inversedBy="typePrestationMentions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type_prestation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getTypePrestation(): ?TypePrestation
    {
        return $this->type_prestation;
    }

    public function setTypePrestation(?TypePrestation $type_prestation): self
    {
        $this->type_prestation = $type_prestation;

        return $this;
    }
}
