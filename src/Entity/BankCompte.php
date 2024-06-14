<?php

namespace App\Entity;

use App\Repository\BankCompteRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=BankCompteRepository::class)
 */
class BankCompte
{
    /**
     *
     */
    const ECOLAGE       = 'ECOLAGE';
    const VACATION      = 'VACATION';
    const FRAIS_SCOLARITE      = 'FRAIS_SCOLARITE';

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addConstraint(
            new UniqueEntity(
                [
                    'fields' => ['resource', 'mention', 'niveau', 'parcours'],
                    'ignoreNull' => false,
                    'message' => 'Numéro déjà utilisé'
                ]
            )
        );
    }

    /**
     * @var array
     */
    static $resourceList = [
        'Ecolage'    => self::ECOLAGE,
        'Vacation' => self::VACATION,
        'Frais de scolarité' => self::FRAIS_SCOLARITE
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="bankComptes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="bankComptes")
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="bankComptes")
     */
    private $parcours;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }
}
