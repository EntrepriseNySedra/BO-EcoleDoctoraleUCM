<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity(repositoryClass=EtudiantRepository::class)
 * @UniqueEntity("immatricule")
 * @ORM\HasLifecycleCallbacks()
 */
class Etudiant
{
    const STATUS_DESACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DENIED_ECOLAGE = -1;
    const STATUS_DENIED_ABSENCE = -2;

    static $maritalStatus = [
        'Celibataire' => 'Celibataire',
        'Marié(e)'    => 'Marié(e)',
    ];

    static $baccSeries = [
        'L' => 'L',
        'S' => 'S'
    ];

    static $religiousProfilOptions = [
        'Frère'  => 'Frère',
        'Sœur'   => 'Sœur',
        'Père'   => 'Père'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     */
    private $email;

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
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Absences::class, mappedBy="etudiant_uuid", orphanRemoval=true)
     */
    private $absences;

    /**
     * @ORM\OneToMany(targetEntity=Notes::class, mappedBy="etudiant")
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="etudiants")
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="etudiants")
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="etudiants")
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=Civility::class, inversedBy="etudiants")
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, unique=true)
     */
    private $immatricule;

   /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $cinNum;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $cinDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cinDeliveryLocation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lettreMotivation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lettrePresentation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $certMedical;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $acteNaissance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_file;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bacc_annee;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $bacc_serie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_mention;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cin_file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autreDocLibelle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autreDocFichier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $birthPlace;
    
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $baccNuminscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religion;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $situationMatrimoniale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fatherName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fatherAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fatherJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fatherJobAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motherName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motherAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motherJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motherJobAddressContact;

    /**
     * @ORM\ManyToOne(targetEntity=Civility::class)
     */
    private $tutorCivility;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tutorFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tutorLastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tutorAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tutorJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tutorJobAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jointFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jointLastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $jointAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jointJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $jointJobAddressContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religiousCongregationName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religiousAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religiousResponsibleName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $religiousPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religiousEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religiousProfil;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $passeportNum;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $passportDeliveryDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $residenceTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passportDeliveryPlace;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstCycleEtablissement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstCycleDiplome;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $firstCycleYear;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondCycleEtablissement1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondCycleEtablissement2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondCycleDiplome1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondCycleDiplome2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $secondCycleYear1;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $secondCycleYear2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelEtab1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelEtab2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelEtab3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelDiplome1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelDiplome2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelDiplome3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherLevelYear1;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherLevelYear2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherLevelYear3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageEtab1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageEtab2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageEtab3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageDiplome1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageDiplome2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageDiplome3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageYear1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherStageYear2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherStageYear3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationEtab1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationEtab2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationEtab3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationDiplome1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationDiplome2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $otherFormationDiplome3;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherFormationYear1;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherFormationYear2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $otherFormationYear3;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $sport;

    /**
     * @ORM\OneToMany(targetEntity=Inscription::class, mappedBy="etudiant")
     */
    private $inscriptions;

    /**
     * @ORM\OneToMany(targetEntity=FraisScolarite::class, mappedBy="etudiant")
     */
    private $fraisScolarites;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=EtudiantDocument::class, mappedBy="etudiant", orphanRemoval=true)
     */
    private $etudiantDocuments;

    /**
     * Etudiant constructor.
     */
    public function __construct()
    {
        $this->uuid     = Uuid::uuid4();
        $this->absences = new ArrayCollection();
        $this->notes    = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->demandeDocs = new ArrayCollection();
        $this->fraisScolarites = new ArrayCollection();
        $this->etudiantDocuments = new ArrayCollection();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('bacc_annee', new Assert\NotNull([
                'message' => 'Veuillez saisir une année'
            ])
        );
        $metadata->addPropertyConstraint('birthDate', new Assert\NotNull([
                'message' => 'Veuillez saisir la date de naissance'
            ])
        );
        // $metadata->addPropertyConstraint('cinDeliveryDate', new Assert\NotNull([
        //         'message' => 'Veuillez saisir la date de delivrance'
        //     ])
        // );        
    }

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
    public function getUuid() : ?string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return \App\Entity\Etudiant
     */
    public function setUuid(string $uuid) : self
    {
        $this->uuid = $uuid;

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
     * @param string $firstName
     *
     * @return \App\Entity\Etudiant
     */
    public function setFirstName(string $firstName) : self
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
     * @param string|null $lastName
     *
     * @return \App\Entity\Etudiant
     */
    public function setLastName(?string $lastName) : self
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
     * @return \App\Entity\Etudiant
     */
    public function setBirthDate(?\DateTimeInterface $birthDate) : self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress() : ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return \App\Entity\Etudiant
     */
    public function setAddress(?string $address) : self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone() : ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     *
     * @return \App\Entity\Etudiant
     */
    public function setPhone(?string $phone) : self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return \App\Entity\Etudiant
     */
    public function setEmail(?string $email) : self
    {
        $this->email = $email;

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
     * @return \App\Entity\Etudiant
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
     * @return \App\Entity\Etudiant
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
     * @return \App\Entity\Etudiant
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \App\Entity\User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User|null $user
     *
     * @return \App\Entity\Etudiant
     */
    public function setUser(?User $user) : self
    {
        $this->user = $user;

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
     * @return Collection|Absences[]
     */
    public function getAbsences() : Collection
    {
        return $this->absences;
    }

    /**
     * @param \App\Entity\Absences $absence
     *
     * @return \App\Entity\Etudiant
     */
    public function addAbsence(Absences $absence) : self
    {
        if (!$this->absences->contains($absence)) {
            $this->absences[] = $absence;
            $absence->setEtudiantUuid($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Absences $absence
     *
     * @return \App\Entity\Etudiant
     */
    public function removeAbsence(Absences $absence) : self
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getEtudiantUuid() === $this) {
                $absence->setEtudiantUuid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notes[]
     */
    public function getNotes() : Collection
    {
        return $this->notes;
    }

    /**
     * @param \App\Entity\Notes $note
     *
     * @return \App\Entity\Etudiant
     */
    public function addNote(Notes $note) : self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setEtudiant($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Notes $note
     *
     * @return \App\Entity\Etudiant
     */
    public function removeNote(Notes $note) : self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getEtudiant() === $this) {
                $note->setEtudiant(null);
            }
        }

        return $this;
    }

    /**
     * @return \App\Entity\Niveau|null
     */
    public function getNiveau() : ?Niveau
    {
        return $this->niveau;
    }

    /**
     * @param \App\Entity\Niveau|null $niveau
     *
     * @return \App\Entity\Etudiant
     */
    public function setNiveau(?Niveau $niveau) : self
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * @return \App\Entity\Mention|null
     */
    public function getMention() : ?Mention
    {
        return $this->mention;
    }

    /**
     * @param \App\Entity\Mention|null $mention
     *
     * @return \App\Entity\Etudiant
     */
    public function setMention(?Mention $mention) : self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getParcours() : ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours) : self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getCivility() : ?Civility
    {
        return $this->civility;
    }

    public function setCivility(?Civility $civility) : self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getNationality() : ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality) : self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getImmatricule() : ?string
    {
        return $this->immatricule;
    }

    public function setImmatricule(?string $immatricule) : self
    {
        $this->immatricule = $immatricule;

        return $this;
    }

    public function getCinNum() : ?string
    {
        return $this->cinNum;
    }

    /*public function setCinNum(string $cinNum) : self
    {
        $this->cinNum = $cinNum;

        return $this;
    }*/
    public function setCinNum(string $cinNum = null): self
    {
        $this->cinNum = $cinNum;
        return $this;
    }

    public function getCinDeliveryDate() : ?\DateTimeInterface
    {
        return $this->cinDeliveryDate;
    }

    public function setCinDeliveryDate(?\DateTimeInterface $cinDeliveryDate) : self
    {
        $this->cinDeliveryDate = $cinDeliveryDate;

        return $this;
    }

    public function getCinDeliveryLocation() : ?string
    {
        return $this->cinDeliveryLocation;
    }

    public function setCinDeliveryLocation(?string $cinDeliveryLocation) : self
    {
        $this->cinDeliveryLocation = $cinDeliveryLocation;

        return $this;
    }

    public function getLettreMotivation() : ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(?string $lettreMotivation) : self
    {
        $this->lettreMotivation = $lettreMotivation;

        return $this;
    }

    public function getLettrePresentation() : ?string
    {
        return $this->lettrePresentation;
    }

    public function setLettrePresentation(?string $lettrePresentation) : self
    {
        $this->lettrePresentation = $lettrePresentation;

        return $this;
    }

    public function getCv() : ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv) : self
    {
        $this->cv = $cv;

        return $this;
    }

    public function getPhoto1() : ?string
    {
        return $this->photo1;
    }

    public function setPhoto1(?string $photo1) : self
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function getPhoto2() : ?string
    {
        return $this->photo2;
    }

    public function setPhoto2(?string $photo2) : self
    {
        $this->photo2 = $photo2;

        return $this;
    }

    public function getCertMedical() : ?string
    {
        return $this->certMedical;
    }

    public function setCertMedical(?string $certMedical) : self
    {
        $this->certMedical = $certMedical;

        return $this;
    }

    public function getActeNaissance() : ?string
    {
        return $this->acteNaissance;
    }

    public function setActeNaissance(?string $acteNaissance) : self
    {
        $this->acteNaissance = $acteNaissance;

        return $this;
    }

    public function getBaccFile() : ?string
    {
        return $this->bacc_file;
    }

    public function setBaccFile(?string $bacc_file) : self
    {
        $this->bacc_file = $bacc_file;

        return $this;
    }

    public function getBaccAnnee() : ?int
    {
        return $this->bacc_annee;
    }

    public function setBaccAnnee(?int $bacc_annee) : self
    {
        $this->bacc_annee = $bacc_annee;

        return $this;
    }

    public function getBaccSerie() : ?string
    {
        return $this->bacc_serie;
    }

    public function setBaccSerie(?string $bacc_serie) : self
    {
        $this->bacc_serie = $bacc_serie;

        return $this;
    }

    public function getBaccMention() : ?string
    {
        return $this->bacc_mention;
    }

    public function setBaccMention(?string $bacc_mention) : self
    {
        $this->bacc_mention = $bacc_mention;

        return $this;
    }

    public function getCinFile() : ?string
    {
        return $this->cin_file;
    }

    public function setCinFile(?string $cin_file) : self
    {
        $this->cin_file = $cin_file;

        return $this;
    }

    public function getAutreDocLibelle() : ?string
    {
        return $this->autreDocLibelle;
    }

    public function setAutreDocLibelle(?string $autreDocLibelle) : self
    {
        $this->autreDocLibelle = $autreDocLibelle;

        return $this;
    }

    public function getAutreDocFichier() : ?string
    {
        return $this->autreDocFichier;
    }

    public function setAutreDocFichier(?string $autreDocFichier) : self
    {
        $this->autreDocFichier = $autreDocFichier;

        return $this;
    }

    public function getBirthPlace() : ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace) : self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getBaccNuminscription() : ?string
    {
        return $this->baccNuminscription;
    }

    public function setBaccNuminscription(?string $baccNuminscription) : self
    {
        $this->baccNuminscription = $baccNuminscription;

        return $this;
    }

    public function getReligion() : ?string
    {
        return $this->religion;
    }

    public function setReligion(?string $religion) : self
    {
        $this->religion = $religion;

        return $this;
    }

    public function getSituationMatrimoniale() : ?string
    {
        return $this->situationMatrimoniale;
    }

    public function setSituationMatrimoniale(?string $situationMatrimoniale) : self
    {
        $this->situationMatrimoniale = $situationMatrimoniale;

        return $this;
    }

    public function getFatherName() : ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(?string $fatherName) : self
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    public function getFatherAddressContact() : ?string
    {
        return $this->fatherAddressContact;
    }

    public function setFatherAddressContact(?string $fatherAddressContact) : self
    {
        $this->fatherAddressContact = $fatherAddressContact;

        return $this;
    }

    public function getFatherJob() : ?string
    {
        return $this->fatherJob;
    }

    public function setFatherJob(?string $fatherJob) : self
    {
        $this->fatherJob = $fatherJob;

        return $this;
    }

    public function getFatherJobAddressContact() : ?string
    {
        return $this->fatherJobAddressContact;
    }

    public function setFatherJobAddressContact(?string $fatherJobAddressContact) : self
    {
        $this->fatherJobAddressContact = $fatherJobAddressContact;

        return $this;
    }

    public function getMotherName() : ?string
    {
        return $this->motherName;
    }

    public function setMotherName(?string $motherName) : self
    {
        $this->motherName = $motherName;

        return $this;
    }

    public function getMotherAddressContact() : ?string
    {
        return $this->motherAddressContact;
    }

    public function setMotherAddressContact(?string $motherAddressContact) : self
    {
        $this->motherAddressContact = $motherAddressContact;

        return $this;
    }

    public function getMotherJob() : ?string
    {
        return $this->motherJob;
    }

    public function setMotherJob(?string $motherJob) : self
    {
        $this->motherJob = $motherJob;

        return $this;
    }

    public function getMotherJobAddressContact() : ?string
    {
        return $this->motherJobAddressContact;
    }

    public function setMotherJobAddressContact(?string $motherJobAddressContact) : self
    {
        $this->motherJobAddressContact = $motherJobAddressContact;

        return $this;
    }

    public function getTutorCivility() : ?Civility
    {
        return $this->tutorCivility;
    }

    public function setTutorCivility(?Civility $tutorCivility) : self
    {
        $this->tutorCivility = $tutorCivility;

        return $this;
    }

    public function getTutorFirstName() : ?string
    {
        return $this->tutorFirstName;
    }

    public function setTutorFirstName(?string $tutorFirstName) : self
    {
        $this->tutorFirstName = $tutorFirstName;

        return $this;
    }

    public function getTutorLastName() : ?string
    {
        return $this->tutorLastName;
    }

    public function setTutorLastName(?string $tutorLastName) : self
    {
        $this->tutorLastName = $tutorLastName;

        return $this;
    }

    public function getTutorAddressContact() : ?string
    {
        return $this->tutorAddressContact;
    }

    public function setTutorAddressContact(?string $tutorAddressContact) : self
    {
        $this->tutorAddressContact = $tutorAddressContact;

        return $this;
    }

    public function getTutorJob() : ?string
    {
        return $this->tutorJob;
    }

    public function setTutorJob(?string $tutorJob) : self
    {
        $this->tutorJob = $tutorJob;

        return $this;
    }

    public function getTutorJobAddressContact() : ?string
    {
        return $this->tutorJobAddressContact;
    }

    public function setTutorJobAddressContact(?string $tutorJobAddressContact) : self
    {
        $this->tutorJobAddressContact = $tutorJobAddressContact;

        return $this;
    }

    public function getJointFirstName() : ?string
    {
        return $this->jointFirstName;
    }

    public function setJointFirstName(?string $jointFirstName) : self
    {
        $this->jointFirstName = $jointFirstName;

        return $this;
    }

    public function getJointLastName() : ?string
    {
        return $this->jointLastName;
    }

    public function setJointLastName(?string $jointLastName) : self
    {
        $this->jointLastName = $jointLastName;

        return $this;
    }

    public function getJointAddressContact() : ?string
    {
        return $this->jointAddressContact;
    }

    public function setJointAddressContact(?string $jointAddressContact) : self
    {
        $this->jointAddressContact = $jointAddressContact;

        return $this;
    }

    public function getJointJob() : ?string
    {
        return $this->jointJob;
    }

    public function setJointJob(?string $jointJob) : self
    {
        $this->jointJob = $jointJob;

        return $this;
    }

    public function getJointJobAddressContact() : ?string
    {
        return $this->jointJobAddressContact;
    }

    public function setJointJobAddressContact(?string $jointJobAddressContact) : self
    {
        $this->jointJobAddressContact = $jointJobAddressContact;

        return $this;
    }

    public function getReligiousCongregationName() : ?string
    {
        return $this->religiousCongregationName;
    }

    public function setReligiousCongregationName(?string $religiousCongregationName) : self
    {
        $this->religiousCongregationName = $religiousCongregationName;

        return $this;
    }

    public function getReligiousAddress() : ?string
    {
        return $this->religiousAddress;
    }

    public function setReligiousAddress(?string $religiousAddress) : self
    {
        $this->religiousAddress = $religiousAddress;

        return $this;
    }

    public function getReligiousResponsibleName() : ?string
    {
        return $this->religiousResponsibleName;
    }

    public function setReligiousResponsibleName(?string $religiousResponsibleName) : self
    {
        $this->religiousResponsibleName = $religiousResponsibleName;

        return $this;
    }

    public function getReligiousPhone() : ?string
    {
        return $this->religiousPhone;
    }

    public function setReligiousPhone(?string $religiousPhone) : self
    {
        $this->religiousPhone = $religiousPhone;

        return $this;
    }

    public function getReligiousEmail() : ?string
    {
        return $this->religiousEmail;
    }

    public function setReligiousEmail(?string $religiousEmail) : self
    {
        $this->religiousEmail = $religiousEmail;

        return $this;
    }

    public function getReligiousProfil() : ?string
    {
        return $this->religiousProfil;
    }

    public function setReligiousProfil(?string $religiousProfil) : self
    {
        $this->religiousProfil = $religiousProfil;

        return $this;
    }

    public function getPasseportNum() : ?string
    {
        return $this->passeportNum;
    }

    public function setPasseportNum(?string $passeportNum) : self
    {
        $this->passeportNum = $passeportNum;

        return $this;
    }

    public function getPassportDeliveryDate() : ?\DateTimeInterface
    {
        return $this->passportDeliveryDate;
    }

    public function setPassportDeliveryDate(?\DateTimeInterface $passportDeliveryDate) : self
    {
        $this->passportDeliveryDate = $passportDeliveryDate;

        return $this;
    }

    public function getResidenceTitle() : ?string
    {
        return $this->residenceTitle;
    }

    public function setResidenceTitle(?string $residenceTitle) : self
    {
        $this->residenceTitle = $residenceTitle;

        return $this;
    }

    public function getPassportDeliveryPlace() : ?string
    {
        return $this->passportDeliveryPlace;
    }

    public function setPassportDeliveryPlace(?string $passportDeliveryPlace) : self
    {
        $this->passportDeliveryPlace = $passportDeliveryPlace;

        return $this;
    }

    public function getFirstCycleEtablissement() : ?string
    {
        return $this->firstCycleEtablissement;
    }

    public function setFirstCycleEtablissement(?string $firstCycleEtablissement) : self
    {
        $this->firstCycleEtablissement = $firstCycleEtablissement;

        return $this;
    }

    public function getFirstCycleDiplome() : ?string
    {
        return $this->firstCycleDiplome;
    }

    public function setFirstCycleDiplome(?string $firstCycleDiplome) : self
    {
        $this->firstCycleDiplome = $firstCycleDiplome;

        return $this;
    }

    public function getFirstCycleYear() : ?string
    {
        return $this->firstCycleYear;
    }

    public function setFirstCycleYear(?string $firstCycleYear) : self
    {
        $this->firstCycleYear = $firstCycleYear;

        return $this;
    }

    public function getSecondCycleEtablissement1() : ?string
    {
        return $this->secondCycleEtablissement1;
    }

    public function setSecondCycleEtablissement1(?string $secondCycleEtablissement1) : self
    {
        $this->secondCycleEtablissement1 = $secondCycleEtablissement1;

        return $this;
    }

    public function getSecondCycleEtablissement2() : ?string
    {
        return $this->secondCycleEtablissement2;
    }

    public function setSecondCycleEtablissement2(?string $secondCycleEtablissement2) : self
    {
        $this->secondCycleEtablissement2 = $secondCycleEtablissement2;

        return $this;
    }

    public function getSecondCycleDiplome1() : ?string
    {
        return $this->secondCycleDiplome1;
    }

    public function setSecondCycleDiplome1(?string $secondCycleDiplome1) : self
    {
        $this->secondCycleDiplome1 = $secondCycleDiplome1;

        return $this;
    }

    public function getSecondCycleDiplome2() : ?string
    {
        return $this->secondCycleDiplome2;
    }

    public function setSecondCycleDiplome2(?string $secondCycleDiplome2) : self
    {
        $this->secondCycleDiplome2 = $secondCycleDiplome2;

        return $this;
    }

    public function getSecondCycleYear1() : ?string
    {
        return $this->secondCycleYear1;
    }

    public function setSecondCycleYear1(?string $secondCycleYear1) : self
    {
        $this->secondCycleYear1 = $secondCycleYear1;

        return $this;
    }

    public function getSecondCycleYear2() : ?string
    {
        return $this->secondCycleYear2;
    }

    public function setSecondCycleYear2(?string $secondCycleYear2) : self
    {
        $this->secondCycleYear2 = $secondCycleYear2;

        return $this;
    }

    public function getOtherLevelEtab1() : ?string
    {
        return $this->otherLevelEtab1;
    }

    public function setOtherLevelEtab1(?string $otherLevelEtab1) : self
    {
        $this->otherLevelEtab1 = $otherLevelEtab1;

        return $this;
    }

    public function getOtherLevelEtab2() : ?string
    {
        return $this->otherLevelEtab2;
    }

    public function setOtherLevelEtab2(?string $otherLevelEtab2) : self
    {
        $this->otherLevelEtab2 = $otherLevelEtab2;

        return $this;
    }

    public function getOtherLevelEtab3() : ?string
    {
        return $this->otherLevelEtab3;
    }

    public function setOtherLevelEtab3(?string $otherLevelEtab3) : self
    {
        $this->otherLevelEtab3 = $otherLevelEtab3;

        return $this;
    }

    public function getOtherLevelDiplome1() : ?string
    {
        return $this->otherLevelDiplome1;
    }

    public function setOtherLevelDiplome1(?string $otherLevelDiplome1) : self
    {
        $this->otherLevelDiplome1 = $otherLevelDiplome1;

        return $this;
    }

    public function getOtherLevelDiplome2() : ?string
    {
        return $this->otherLevelDiplome2;
    }

    public function setOtherLevelDiplome2(?string $otherLevelDiplome2) : self
    {
        $this->otherLevelDiplome2 = $otherLevelDiplome2;

        return $this;
    }

    public function getOtherLevelDiplome3() : ?string
    {
        return $this->otherLevelDiplome3;
    }

    public function setOtherLevelDiplome3(?string $otherLevelDiplome3) : self
    {
        $this->otherLevelDiplome3 = $otherLevelDiplome3;

        return $this;
    }

    public function getOtherLevelYear1() : ?string
    {
        return $this->otherLevelYear1;
    }

    public function setOtherLevelYear1(?string $otherLevelYear1) : self
    {
        $this->otherLevelYear1 = $otherLevelYear1;

        return $this;
    }

    public function getOtherLevelYear2() : ?string
    {
        return $this->otherLevelYear2;
    }

    public function setOtherLevelYear2(?string $otherLevelYear2) : self
    {
        $this->otherLevelYear2 = $otherLevelYear2;

        return $this;
    }

    public function getOtherLevelYear3() : ?string
    {
        return $this->otherLevelYear3;
    }

    public function setOtherLevelYear3(?string $otherLevelYear3) : self
    {
        $this->otherLevelYear3 = $otherLevelYear3;

        return $this;
    }

    public function getOtherStageEtab1() : ?string
    {
        return $this->otherStageEtab1;
    }

    public function setOtherStageEtab1(?string $otherStageEtab1) : self
    {
        $this->otherStageEtab1 = $otherStageEtab1;

        return $this;
    }

    public function getOtherStageEtab2() : ?string
    {
        return $this->otherStageEtab2;
    }

    public function setOtherStageEtab2(?string $otherStageEtab2) : self
    {
        $this->otherStageEtab2 = $otherStageEtab2;

        return $this;
    }

    public function getOtherStageEtab3() : ?string
    {
        return $this->otherStageEtab3;
    }

    public function setOtherStageEtab3(?string $otherStageEtab3) : self
    {
        $this->otherStageEtab3 = $otherStageEtab3;

        return $this;
    }

    public function getOtherStageDiplome1() : ?string
    {
        return $this->otherStageDiplome1;
    }

    public function setOtherStageDiplome1(?string $otherStageDiplome1) : self
    {
        $this->otherStageDiplome1 = $otherStageDiplome1;

        return $this;
    }

    public function getOtherStageDiplome2() : ?string
    {
        return $this->otherStageDiplome2;
    }

    public function setOtherStageDiplome2(?string $otherStageDiplome2) : self
    {
        $this->otherStageDiplome2 = $otherStageDiplome2;

        return $this;
    }

    public function getOtherStageDiplome3() : ?string
    {
        return $this->otherStageDiplome3;
    }

    public function setOtherStageDiplome3(?string $otherStageDiplome3) : self
    {
        $this->otherStageDiplome3 = $otherStageDiplome3;

        return $this;
    }

    public function getOtherStageYear1() : ?string
    {
        return $this->otherStageYear1;
    }

    public function setOtherStageYear1(?string $otherStageYear1) : self
    {
        $this->otherStageYear1 = $otherStageYear1;

        return $this;
    }

    public function getOtherStageYear2() : ?string
    {
        return $this->otherStageYear2;
    }

    public function setOtherStageYear2(?string $otherStageYear2) : self
    {
        $this->otherStageYear2 = $otherStageYear2;

        return $this;
    }

    public function getOtherStageYear3() : ?string
    {
        return $this->otherStageYear3;
    }

    public function setOtherStageYear3(?string $otherStageYear3) : self
    {
        $this->otherStageYear3 = $otherStageYear3;

        return $this;
    }

    public function getOtherFormationEtab1() : ?string
    {
        return $this->otherFormationEtab1;
    }

    public function setOtherFormationEtab1(?string $otherFormationEtab1) : self
    {
        $this->otherFormationEtab1 = $otherFormationEtab1;

        return $this;
    }

    public function getOtherFormationEtab2() : ?string
    {
        return $this->otherFormationEtab2;
    }

    public function setOtherFormationEtab2(?string $otherFormationEtab2) : self
    {
        $this->otherFormationEtab2 = $otherFormationEtab2;

        return $this;
    }

    public function getOtherFormationEtab3() : ?string
    {
        return $this->otherFormationEtab3;
    }

    public function setOtherFormationEtab3(?string $otherFormationEtab3) : self
    {
        $this->otherFormationEtab3 = $otherFormationEtab3;

        return $this;
    }

    public function getOtherFormationDiplome1() : ?string
    {
        return $this->otherFormationDiplome1;
    }

    public function setOtherFormationDiplome1(?string $otherFormationDiplome1) : self
    {
        $this->otherFormationDiplome1 = $otherFormationDiplome1;

        return $this;
    }

    public function getOtherFormationDiplome2() : ?string
    {
        return $this->otherFormationDiplome2;
    }

    public function setOtherFormationDiplome2(?string $otherFormationDiplome2) : self
    {
        $this->otherFormationDiplome2 = $otherFormationDiplome2;

        return $this;
    }

    public function getOtherFormationDiplome3() : ?string
    {
        return $this->otherFormationDiplome3;
    }

    public function setOtherFormationDiplome3(?string $otherFormationDiplome3) : self
    {
        $this->otherFormationDiplome3 = $otherFormationDiplome3;

        return $this;
    }

    public function getOtherFormationYear1() : ?string
    {
        return $this->otherFormationYear1;
    }

    public function setOtherFormationYear1(?string $otherFormationYear1) : self
    {
        $this->otherFormationYear1 = $otherFormationYear1;

        return $this;
    }

    public function getOtherFormationYear2() : ?string
    {
        return $this->otherFormationYear2;
    }

    public function setOtherFormationYear2(?string $otherFormationYear2) : self
    {
        $this->otherFormationYear2 = $otherFormationYear2;

        return $this;
    }

    public function getOtherFormationYear3() : ?string
    {
        return $this->otherFormationYear3;
    }

    public function setOtherFormationYear3(?string $otherFormationYear3) : self
    {
        $this->otherFormationYear3 = $otherFormationYear3;

        return $this;
    }

    public function getSport() : ?string
    {
        return $this->sport;
    }

    public function setSport(?string $sport) : self
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * @return Collection|Inscription[]
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions[] = $inscription;
            $inscription->setEtudiant($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getEtudiant() === $this) {
                $inscription->setEtudiant(null);
            }
        }

        return $this;
    }

    public function fullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return Collection|DemandeDoc[]
     */
    public function getDemandeDocs(): Collection
    {
        return $this->demandeDocs;
    }

    public function addDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if (!$this->demandeDocs->contains($demandeDoc)) {
            $this->demandeDocs[] = $demandeDoc;
            $demandeDoc->setEtudiant($this);
        }

        return $this;
    }

    public function removeDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if ($this->demandeDocs->removeElement($demandeDoc)) {
            // set the owning side to null (unless already changed)
            if ($demandeDoc->getEtudiant() === $this) {
                $demandeDoc->setEtudiant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FraisScolarite[]
     */
    public function getFraisScolarites(): Collection
    {
        return $this->fraisScolarites;
    }

    public function addFraisScolarite(FraisScolarite $fraisScolarite): self
    {
        if (!$this->fraisScolarites->contains($fraisScolarite)) {
            $this->fraisScolarites[] = $fraisScolarite;
            $fraisScolarite->setEtudiant($this);
        }

        return $this;
    }

    public function removeFraisScolarite(FraisScolarite $fraisScolarite): self
    {
        if ($this->fraisScolarites->removeElement($fraisScolarite)) {
            // set the owning side to null (unless already changed)
            if ($fraisScolarite->getEtudiant() === $this) {
                $fraisScolarite->setEtudiant(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, EtudiantDocument>
     */
    public function getEtudiantDocuments(): Collection
    {
        return $this->etudiantDocuments;
    }

    public function addEtudiantDocument(EtudiantDocument $etudiantDocument): self
    {
        if (!$this->etudiantDocuments->contains($etudiantDocument)) {
            $this->etudiantDocuments[] = $etudiantDocument;
            $etudiantDocument->setEtudiant($this);
        }

        return $this;
    }

    public function removeEtudiantDocument(EtudiantDocument $etudiantDocument): self
    {
        if ($this->etudiantDocuments->removeElement($etudiantDocument)) {
            // set the owning side to null (unless already changed)
            if ($etudiantDocument->getEtudiant() === $this) {
                $etudiantDocument->setEtudiant(null);
            }
        }

        return $this;
    }
}