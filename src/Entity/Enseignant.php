<?php

namespace App\Entity;

use App\Repository\EnseignantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=EnseignantRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Enseignant
{

    const ACTIVE                = 1; //status 1
    const ACTIVE_KO             = 0; //status 0
    const GENDER_FEMALE         = "FEMME";
    const GENDER_MALE           = "HOMME";

    static $civilityList = [
        'Homme'     => self::GENDER_MALE,
        'Femme'     => self::GENDER_FEMALE
    ];
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $civility;

    /**
     * @ORM\OneToMany(targetEntity=Matiere::class, mappedBy="enseignant")
     */
    private $matieres;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMention::class, mappedBy="enseignant", orphanRemoval=true)
     */
    private $enseignantMentions;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $pathCv;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $pathDiploma;

    /**
     * @ORM\OneToMany(targetEntity=EnseignantMatiere::class, mappedBy="enseignant")
     */
    private $matiere;

    /**
     * @ORM\OneToMany(targetEntity=FichePresenceEnseignant::class, mappedBy="enseignant")
     */
    private $fichePresenceEnseignants;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $immatricule;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bank_num;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bank_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tiers_count;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birth_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $whatsapp;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * Enseignant constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->matieres = new ArrayCollection();
        $this->enseignantMentions = new ArrayCollection();
        $this->matiere = new ArrayCollection();
        $this->fichePresenceEnseignants = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User|null $user
     *
     * @return \App\Entity\Enseignant
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return \App\Entity\Enseignant
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return \App\Entity\Enseignant
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return \App\Entity\Enseignant
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return \App\Entity\Enseignant
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     *
     * @return \App\Entity\Enseignant
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     *
     * @return \App\Entity\Enseignant
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return \App\Entity\Enseignant
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCivility(): ?string
    {
        return $this->civility;
    }

    /**
     * @param string|null $civility
     *
     * @return \App\Entity\Enseignant
     */
    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * @return Collection|Matiere[]
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    /**
     * @param \App\Entity\Matiere $matiere
     *
     * @return \App\Entity\Enseignant
     */
    public function addMatiere(Matiere $matiere): self
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres[] = $matiere;
            $matiere->setEnseignant($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Matiere $matiere
     *
     * @return \App\Entity\Enseignant
     */
    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getEnseignant() === $this) {
                $matiere->setEnseignant(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function fullName()
    {
        return $this->firstName . ' ' . $this->lastName;
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
     * @return Collection|EnseignantMention[]
     */
    public function getEnseignantMentions(): Collection
    {
        return $this->enseignantMentions;
    }

    public function addEnseignantMention(EnseignantMention $enseignantMention): self
    {
        if (!$this->enseignantMentions->contains($enseignantMention)) {
            $this->enseignantMentions[] = $enseignantMention;
            $enseignantMention->setEnseignant($this);
        }

        return $this;
    }

    public function removeEnseignantMention(EnseignantMention $enseignantMention): self
    {
        if ($this->enseignantMentions->removeElement($enseignantMention)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMention->getEnseignant() === $this) {
                $enseignantMention->setEnseignant(null);
            }
        }

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

    public function getPathCv(): ?string
    {
        return $this->pathCv;
    }

    public function setPathCv(?string $pathCv): self
    {
        $this->pathCv = $pathCv;

        return $this;
    }

    public function getPathDiploma(): ?string
    {
        return $this->pathDiploma;
    }

    public function setPathDiploma(?string $pathDiploma): self
    {
        $this->pathDiploma = $pathDiploma;

        return $this;
    }

    /**
     * @return Collection|EnseignantMatiere[]
     */
    public function getMatiere(): Collection
    {
        return $this->matiere;
    }

    /**
     * @return Collection|FichePresenceEnseignant[]
     */
    public function getFichePresenceEnseignants(): Collection
    {
        return $this->fichePresenceEnseignants;
    }

    public function addFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if (!$this->fichePresenceEnseignants->contains($fichePresenceEnseignant)) {
            $this->fichePresenceEnseignants[] = $fichePresenceEnseignant;
            $fichePresenceEnseignant->setEnseignant($this);
        }

        return $this;
    }

    public function removeFichePresenceEnseignant(FichePresenceEnseignant $fichePresenceEnseignant): self
    {
        if ($this->fichePresenceEnseignants->removeElement($fichePresenceEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($fichePresenceEnseignant->getEnseignant() === $this) {
                $fichePresenceEnseignant->setEnseignant(null);
            }
        }

        return $this;
    }

    public function getImmatricule(): ?string
    {
        return $this->immatricule;
    }

    public function setImmatricule(?string $immatricule): self
    {
        $this->immatricule = $immatricule;

        return $this;
    }

    public function getBankNum(): ?string
    {
        return $this->bank_num;
    }

    public function setBankNum(?string $bank_num): self
    {
        $this->bank_num = $bank_num;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bank_name;
    }

    public function setBankName(?string $bank_name): self
    {
        $this->bank_name = $bank_name;

        return $this;
    }

    public function getTiersCount(): ?string
    {
        return $this->tiers_count;
    }

    public function setTiersCount(?string $tiers_count): self
    {
        $this->tiers_count = $tiers_count;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(?\DateTimeInterface $birth_date): self
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): self
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
