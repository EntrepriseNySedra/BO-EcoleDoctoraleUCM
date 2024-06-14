<?php

namespace App\Entity;

use App\Repository\DemandeDocRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeDocRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class DemandeDoc
{

    /**
     * @var string
     */
    const CUVILITY_MR  = "Mr";

    /**
     * @var string
     */
    const CUVILITY_MRS = "Mme";
    const CUVILITY_MLLE = "Mlle";

    /**
     * @var array
     */
    static $civilitiesList = [
        self::CUVILITY_MR   => self::CUVILITY_MR,
        self::CUVILITY_MRS  => self::CUVILITY_MRS,
        self::CUVILITY_MLLE => self::CUVILITY_MLLE
    ];

    const TYPE_LETTRE_INTRO     = 'LETTRE_INTRODUCTION';
    const TYPE_RELEVE_NOTE      = 'RELEVE_NOTE';
    const TYPE_DIPLOME   = 'DIPLOME';
    const TYPE_CERTIFICAT_DE_SCOLARITE  = 'CERTIFICAT_DE_SCOLARITE';

    const STATUS_CREATED = 'CREATED';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_VALIDATED = 'VALIDATED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_ARCHIVED = 'ARCHIVED';
    
    /**
     * @var array
     */
    static $statutList = [
        'Crée'      => 'CREATED',
        'Vérifié'   => 'VERIFIED',
        'Validé'    => 'VALIDATED',
        'Rejeté'    => 'REJECTED',
        'Archivé'   => 'ARCHIVED'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $matricule;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $birthPlace;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $treatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeDocType::class, inversedBy="demandeDocs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="demandeDocs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="demandeDocs")
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="demandeDocs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $diplome_year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $univ_year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $postal_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $portable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address_pro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $job;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $diplome_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diplome_libelle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identity_piece;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $depot_attestation;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $diplome_mention;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class)
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getQuantity() : ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setQuantity(int $quantity) : self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMatricule() : ?string
    {
        return $this->matricule;
    }

    /**
     * @param string $matricule
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setMatricule(string $matricule) : self
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSexe() : ?string
    {
        return $this->sexe;
    }

    /**
     * @param string|null $sexe
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setSexe(?string $sexe) : self
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCivility() : ?string
    {
        return $this->civility;
    }

    /**
     * @param string|null $civility
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setCivility(?string $civility) : self
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName() : ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setFirstName(?string $firstName) : self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName() : ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setLastName(string $lastName) : self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBirthDate() : ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTimeInterface|null $birthDate
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setBirthDate(?\DateTimeInterface $birthDate) : self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBirthPlace() : ?string
    {
        return $this->birthPlace;
    }

    /**
     * @param string|null $birthPlace
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setBirthPlace(?string $birthPlace) : self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setDescription(?string $description) : self
    {
        $this->description = $description;

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
     * @return \App\Entity\DemandeDoc
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
     * @return \App\Entity\DemandeDoc
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
     * @return \App\Entity\DemandeDoc
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTreatedAt() : ?\DateTimeInterface
    {
        return $this->treatedAt;
    }

    /**
     * @param \DateTimeInterface|null $treatedAt
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setTreatedAt(?\DateTimeInterface $treatedAt) : self
    {
        $this->treatedAt = $treatedAt;

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

    /**
     * @return \App\Entity\DemandeDocType|null
     */
    public function getType() : ?DemandeDocType
    {
        return $this->type;
    }

    /**
     * @param \App\Entity\DemandeDocType|null $type
     *
     * @return \App\Entity\DemandeDoc
     */
    public function setType(?DemandeDocType $type) : self
    {
        $this->type = $type;

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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

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

    public function getDiplomeYear(): ?int
    {
        return $this->diplome_year;
    }

    public function setDiplomeYear(?int $diplome_year): self
    {
        $this->diplome_year = $diplome_year;

        return $this;
    }

    public function getUnivYear(): ?int
    {
        return $this->univ_year;
    }

    public function setUnivYear(?int $univ_year): self
    {
        $this->univ_year = $univ_year;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPortable(): ?string
    {
        return $this->portable;
    }

    public function setPortable(?string $portable): self
    {
        $this->portable = $portable;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddressPro(): ?string
    {
        return $this->address_pro;
    }

    public function setAddressPro(?string $address_pro): self
    {
        $this->address_pro = $address_pro;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getDiplomeName(): ?string
    {
        return $this->diplome_name;
    }

    public function setDiplomeName(?string $diplome_name): self
    {
        $this->diplome_name = $diplome_name;

        return $this;
    }

    public function getDiplomeLibelle(): ?string
    {
        return $this->diplome_libelle;
    }

    public function setDiplomeLibelle(?string $diplome_libelle): self
    {
        $this->diplome_libelle = $diplome_libelle;

        return $this;
    }

    public function getIdentityPiece(): ?string
    {
        return $this->identity_piece;
    }

    public function setIdentityPiece(?string $identity_piece): self
    {
        $this->identity_piece = $identity_piece;

        return $this;
    }

    public function getDepotAttestation(): ?string
    {
        return $this->depot_attestation;
    }

    public function setDepotAttestation(?string $depot_attestation): self
    {
        $this->depot_attestation = $depot_attestation;

        return $this;
    }

    public function getDiplomeMention(): ?string
    {
        return $this->diplome_mention;
    }

    public function setDiplomeMention(?string $diplome_mention): self
    {
        $this->diplome_mention = $diplome_mention;
    }
    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getFormatedBirthDate()
    {
        return $this->birthDate->format('d/m/Y');
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire): self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

}
