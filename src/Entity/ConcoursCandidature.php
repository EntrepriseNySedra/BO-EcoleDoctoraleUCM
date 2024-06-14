<?php

namespace App\Entity;

use App\Repository\ConcoursCandidatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ConcoursCandidatureRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ConcoursCandidature
{
    /**
     *
     */
    const STATUS_CREATED     = 0;
    /**
     *
     */
    const STATUS_APPROVED    = 1;
    /**
     *
     */
    const STATUS_DISAPPROVED = 2;

    const STATUS_SU_VALIDATED = 3;

    /**
     *
     */
    const GENDER_FEMALE = "FEMME";
    /**
     *
     */
    const GENDER_MALE = "HOMME";

    /**
     *
     */
    const LANG_MG  = "Malagasy";
    /**
     *
     */
    const LANG_FR  = "Français";
    /**
     *
     */
    const LANG_ANG = "Anglais";

    const RESULT_ADJOURNED  = 0;
    const RESULT_ADMITTED   = 1;

    const NOTIFIED   = 1;

    // nb item per page
    const PER_PAGE          = 10;


    /** @var array */
    static $statusList = [
        'Validé'    => self::STATUS_APPROVED,
        'Refusé'    => self::STATUS_DISAPPROVED,
    ];

    /**
     * @var array
     */
    static $genderList = [
        'Homme'     => self::GENDER_FEMALE,
        'Femme'     => self::GENDER_MALE
    ];

    /** @var array */
    static $civilityList = [
        'Monsieur'      => 'MR',
        'Madame'        => 'MME',
        'Mademoiselle'  =>  'MLLE'
    ];

    /**
     * @var array
     */
    static $langList = [
        'Malagasy'   => self::LANG_MG,
        'Français'   => self::LANG_FR,
        'Anglais'    => self::LANG_ANG
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
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diplome;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="concoursCandidatures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="concoursCandidatures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=CandidatureHistorique::class, mappedBy="candidature")
     */
    private $candidatureHistoriques;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    private $birth_place;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $cin_num;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $cin_deliver_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cin_deliver_location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $phone_1;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $phone_2;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $phone_3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $job;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $payement_ref;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motif;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diplome_path;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursNotes::class, mappedBy="concoursCandidature")
     */
    private $concoursNotes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $num_inscription;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payment_date;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $confession_religieuse;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $situation_matrimoniale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $father_first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $father_last_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $father_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $father_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $father_job;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $father_job_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $father_job_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mother_first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mother_last_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mother_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $mother_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mother_job;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mother_job_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $mother_job_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $conjoint_first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $conjoint_last_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $conjoint_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $conjoint_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $conjoint_job;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $conjoint_job_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $conjoint_job_phone;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $tuteur_civility;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tuteur_first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tuteur_last_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tuteur_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $tuteur_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tuteur_job;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tuteur_job_address;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $tuteur_job_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religious_congregation_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religious_address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religious_responsable_foyer_name;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $religious_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religious_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $religious_profil;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $bacc_serie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_autre_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_autre_serie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_mention;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_session;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bacc_num_inscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $origin_etablissement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $university_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $faculte_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $university_country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $university_diplome;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $university_diplome_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $university_autre_titre;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $university_year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bacc_annee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payement_ref_path;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payement_ref_date;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class, inversedBy="concoursCandidatures")
     */
    private $parcours;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     */
    private $anneeUniversitaire;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $resultat;

    /**
     * @ORM\ManyToOne(targetEntity=ConcoursCentre::class, inversedBy="concoursCandidatures")
     */
    private $centre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $immatricule;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $email_notification;

    /**
     * ConcoursCandidature constructor.
     */
    public function __construct()
    {
        $this->candidatureHistoriques = new ArrayCollection();
        $this->concoursNotes = new ArrayCollection();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('payement_ref_date', new Assert\NotNull([
                'message' => 'Veuillez saisir la date de paiement'
            ])
        );
        $metadata->addPropertyConstraint('centre', new Assert\NotNull([
                'message' => 'Veuillez séléctionner votre centre'
            ])
        );
        // $metadata->addPropertyConstraint('payement_ref_date', new Assert\NotEqualTo([
        //     'value' => '__/__/____',
        // ]));
        // $metadata->addPropertyConstraint('dateOfBirth', new Assert\NotNull([
        //         'message' => 'Veuillez saisir une date'
        //     ])
        // );
        // $metadata->addPropertyConstraint('dateOfBirth', new Assert\NotEqualTo(
        //     [
        //         'value' => '__/__/____',
        //         'message' => 'Veuillez saisir une date'  
        //     ])
        // );
    }
    /**
    * @Assert\Callback
    */
   public function validate(ExecutionContextInterface $context, $payload)
   {
        if (!$this->getDiplomePath() && !$this->getBaccNumInscription()) {
           $context->buildViolation('Rajouter le diplmôme ou votre numéro d\'inscription!')
               ->atPath('baccNumInscription')
               ->addViolation();
       } 
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
    public function getFirstName() : ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return \App\Entity\ConcoursCandidature
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
     * @param string $lastName
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setLastName(string $lastName) : self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateOfBirth() : ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    /**
     * @param \DateTimeInterface $dateOfBirth
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setDateOfBirth(\DateTimeInterface $dateOfBirth) : self
    {
        $this->dateOfBirth = $dateOfBirth;

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
     * @return \App\Entity\ConcoursCandidature
     */
    public function setEmail(?string $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDiplome() : ?string
    {
        return $this->diplome;
    }

    /**
     * @param string $diplome
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setDiplome(string $diplome) : self
    {
        $this->diplome = $diplome;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus() : ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setStatus(?int $status) : self
    {
        $this->status = $status;

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
     * @return \App\Entity\ConcoursCandidature
     */
    public function setMention(?Mention $mention) : self
    {
        $this->mention = $mention;

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
     * @return \App\Entity\ConcoursCandidature
     */
    public function setNiveau(?Niveau $niveau) : self
    {
        $this->niveau = $niveau;

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
     * @return \App\Entity\ConcoursCandidature
     */
    public function setCreatedAt(\DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        $this->setStatusValue();
        $this->setCreatedAtValue();
    }

    /**
     * @throws \Exception
     */
    private function setCreatedAtValue()
    {
        $this->createdAt = !$this->createdAt ? new \DateTime() : $this->createdAt;
    }

    /**
     *
     */
    private function setStatusValue()
    {
        $this->status = !$this->status ? self::STATUS_CREATED : $this->status;
    }

    /**
     * @return Collection|CandidatureHistorique[]
     */
    public function getCandidatureHistoriques(): Collection
    {
        return $this->candidatureHistoriques;
    }

    /**
     * @param \App\Entity\CandidatureHistorique $candidatureHistorique
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function addCandidatureHistorique(CandidatureHistorique $candidatureHistorique): self
    {
        if (!$this->candidatureHistoriques->contains($candidatureHistorique)) {
            $this->candidatureHistoriques[] = $candidatureHistorique;
            $candidatureHistorique->setCandidature($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\CandidatureHistorique $candidatureHistorique
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function removeCandidatureHistorique(CandidatureHistorique $candidatureHistorique): self
    {
        if ($this->candidatureHistoriques->removeElement($candidatureHistorique)) {
            // set the owning side to null (unless already changed)
            if ($candidatureHistorique->getCandidature() === $this) {
                $candidatureHistorique->setCandidature(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMotif(): ?string
    {
        return $this->motif;
    }

    /**
     * @param string|null $motif
     */
    public function setMotif(?string $motif)
    {
        $this->motif = $motif;
    }

    /**
     * @return string|null
     */
    public function getBirthPlace(): ?string
    {
        return $this->birth_place;
    }

    /**
     * @param string|null $birth_place
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setBirthPlace(?string $birth_place): self
    {
        $this->birth_place = $birth_place;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLang(): ?string
    {
        return $this->lang;
    }

    /**
     * @param string|null $lang
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setLang(?string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param string|null $nationality
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCinNum(): ?string
    {
        return $this->cin_num;
    }

    /**
     * @param string|null $cin_num
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setCinNum(?string $cin_num): self
    {
        $this->cin_num = $cin_num;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCinDeliverDate(): ?\DateTimeInterface
    {
        return $this->cin_deliver_date;
    }

    /**
     * @param \DateTimeInterface|null $cin_deliver_date
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setCinDeliverDate(?\DateTimeInterface $cin_deliver_date): self
    {
        $this->cin_deliver_date = $cin_deliver_date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCinDeliverLocation(): ?string
    {
        return $this->cin_deliver_location;
    }

    /**
     * @param string|null $cin_deliver_location
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setCinDeliverLocation(?string $cin_deliver_location): self
    {
        $this->cin_deliver_location = $cin_deliver_location;

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
     * @return \App\Entity\ConcoursCandidature
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone1(): ?string
    {
        return $this->phone_1;
    }

    /**
     * @param string|null $phone_1
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setPhone1(?string $phone_1): self
    {
        $this->phone_1 = $phone_1;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone2(): ?string
    {
        return $this->phone_2;
    }

    /**
     * @param string|null $phone_2
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setPhone2(?string $phone_2): self
    {
        $this->phone_2 = $phone_2;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone3(): ?string
    {
        return $this->phone_3;
    }

    /**
     * @param string|null $phone_3
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setPhone3(?string $phone_3): self
    {
        $this->phone_3 = $phone_3;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReligion(): ?string
    {
        return $this->religion;
    }

    /**
     * @param string|null $religion
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setReligion(?string $religion): self
    {
        $this->religion = $religion;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getJob(): ?string
    {
        return $this->job;
    }

    /**
     * @param string|null $job
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setJob(?string $job): self
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCv(): ?string
    {
        return $this->cv;
    }

    /**
     * @param string|null $cv
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setCv(?string $cv): self
    {
        $this->cv = $cv;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayementRef(): ?string
    {
        return $this->payement_ref;
    }

    /**
     * @param string $payement_ref
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setPayementRef(string $payement_ref): self
    {
        $this->payement_ref = $payement_ref;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDiplomePath(): ?string
    {
        return $this->diplome_path;
    }

    /**
     * @param string|null $diplome_path
     *
     * @return \App\Entity\ConcoursCandidature
     */
    public function setDiplomePath(?string $diplome_path): self
    {
        $this->diplome_path = $diplome_path;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName() : string
    {
        return (string) $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return Collection|ConcoursNotes[]
     */
    public function getConcoursNotes(): Collection
    {
        return $this->concoursNotes;
    }

    public function addConcoursNote(ConcoursNotes $concoursNote): self
    {
        if (!$this->concoursNotes->contains($concoursNote)) {
            $this->concoursNotes[] = $concoursNote;
            $concoursNote->setConcoursCandidature($this);
        }

        return $this;
    }

    public function removeConcoursNote(ConcoursNotes $concoursNote): self
    {
        if ($this->concoursNotes->removeElement($concoursNote)) {
            // set the owning side to null (unless already changed)
            if ($concoursNote->getConcoursCandidature() === $this) {
                $concoursNote->setConcoursCandidature(null);
            }
        }

        return $this;
    }

    public function getNumInscription(): ?string
    {
        return $this->num_inscription;
    }

    public function setNumInscription(?string $num_inscription): self
    {
        $this->num_inscription = $num_inscription;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->payment_date;
    }

    public function setPaymentDate(?\DateTimeInterface $payment_date): self
    {
        $this->payment_date = $payment_date;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getConfessionReligieuse(): ?string
    {
        return $this->confession_religieuse;
    }

    public function setConfessionReligieuse(?string $confession_religieuse): self
    {
        $this->confession_religieuse = $confession_religieuse;

        return $this;
    }

    public function getSituationMatrimoniale(): ?string
    {
        return $this->situation_matrimoniale;
    }

    public function setSituationMatrimoniale(?string $situation_matrimoniale): self
    {
        $this->situation_matrimoniale = $situation_matrimoniale;

        return $this;
    }

    public function getFatherFirstName(): ?string
    {
        return $this->father_first_name;
    }

    public function setFatherFirstName(?string $father_first_name): self
    {
        $this->father_first_name = $father_first_name;

        return $this;
    }

    public function getFatherLastName(): ?string
    {
        return $this->father_last_name;
    }

    public function setFatherLastName(?string $father_last_name): self
    {
        $this->father_last_name = $father_last_name;

        return $this;
    }

    public function getFatherAddress(): ?string
    {
        return $this->father_address;
    }

    public function setFatherAddress(?string $father_address): self
    {
        $this->father_address = $father_address;

        return $this;
    }

    public function getFatherPhone(): ?string
    {
        return $this->father_phone;
    }

    public function setFatherPhone(?string $father_phone): self
    {
        $this->father_phone = $father_phone;

        return $this;
    }

    public function getFatherJob(): ?string
    {
        return $this->father_job;
    }

    public function setFatherJob(?string $father_job): self
    {
        $this->father_job = $father_job;

        return $this;
    }

    public function getFatherJobAddress(): ?string
    {
        return $this->father_job_address;
    }

    public function setFatherJobAddress(?string $father_job_address): self
    {
        $this->father_job_address = $father_job_address;

        return $this;
    }

    public function getFatherJobPhone(): ?string
    {
        return $this->father_job_phone;
    }

    public function setFatherJobPhone(?string $father_job_phone): self
    {
        $this->father_job_phone = $father_job_phone;

        return $this;
    }

    public function getMotherFirstName(): ?string
    {
        return $this->mother_first_name;
    }

    public function setMotherFirstName(?string $mother_first_name): self
    {
        $this->mother_first_name = $mother_first_name;

        return $this;
    }

    public function getMotherLastName(): ?string
    {
        return $this->mother_last_name;
    }

    public function setMotherLastName(?string $mother_last_name): self
    {
        $this->mother_last_name = $mother_last_name;

        return $this;
    }

    public function getMotherAddress(): ?string
    {
        return $this->mother_address;
    }

    public function setMotherAddress(?string $mother_address): self
    {
        $this->mother_address = $mother_address;

        return $this;
    }

    public function getMotherPhone(): ?string
    {
        return $this->mother_phone;
    }

    public function setMotherPhone(?string $mother_phone): self
    {
        $this->mother_phone = $mother_phone;

        return $this;
    }

    public function getMotherJob(): ?string
    {
        return $this->mother_job;
    }

    public function setMotherJob(?string $mother_job): self
    {
        $this->mother_job = $mother_job;

        return $this;
    }

    public function getMotherJobAddress(): ?string
    {
        return $this->mother_job_address;
    }

    public function setMotherJobAddress(?string $mother_job_address): self
    {
        $this->mother_job_address = $mother_job_address;

        return $this;
    }

    public function getMotherJobPhone(): ?string
    {
        return $this->mother_job_phone;
    }

    public function setMotherJobPhone(?string $mother_job_phone): self
    {
        $this->mother_job_phone = $mother_job_phone;

        return $this;
    }

    public function getConjointFirstName(): ?string
    {
        return $this->conjoint_first_name;
    }

    public function setConjointFirstName(?string $conjoint_first_name): self
    {
        $this->conjoint_first_name = $conjoint_first_name;

        return $this;
    }

    public function getConjointLastName(): ?string
    {
        return $this->conjoint_last_name;
    }

    public function setConjointLastName(?string $conjoint_last_name): self
    {
        $this->conjoint_last_name = $conjoint_last_name;

        return $this;
    }

    public function getConjointAddress(): ?string
    {
        return $this->conjoint_address;
    }

    public function setConjointAddress(?string $conjoint_address): self
    {
        $this->conjoint_address = $conjoint_address;

        return $this;
    }

    public function getConjointPhone(): ?string
    {
        return $this->conjoint_phone;
    }

    public function setConjointPhone(?string $conjoint_phone): self
    {
        $this->conjoint_phone = $conjoint_phone;

        return $this;
    }

    public function getConjointJob(): ?string
    {
        return $this->conjoint_job;
    }

    public function setConjointJob(?string $conjoint_job): self
    {
        $this->conjoint_job = $conjoint_job;

        return $this;
    }

    public function getConjointJobAddress(): ?string
    {
        return $this->conjoint_job_address;
    }

    public function setConjointJobAddress(?string $conjoint_job_address): self
    {
        $this->conjoint_job_address = $conjoint_job_address;

        return $this;
    }

    public function getConjointJobPhone(): ?string
    {
        return $this->conjoint_job_phone;
    }

    public function setConjointJobPhone(?string $conjoint_job_phone): self
    {
        $this->conjoint_job_phone = $conjoint_job_phone;

        return $this;
    }

    public function getTuteurCivility(): ?string
    {
        return $this->tuteur_civility;
    }

    public function setTuteurCivility(?string $tuteur_civility): self
    {
        $this->tuteur_civility = $tuteur_civility;

        return $this;
    }

    public function getTuteurFirstName(): ?string
    {
        return $this->tuteur_first_name;
    }

    public function setTuteurFirstName(?string $tuteur_first_name): self
    {
        $this->tuteur_first_name = $tuteur_first_name;

        return $this;
    }

    public function getTuteurLastName(): ?string
    {
        return $this->tuteur_last_name;
    }

    public function setTuteurLastName(?string $tuteur_last_name): self
    {
        $this->tuteur_last_name = $tuteur_last_name;

        return $this;
    }

    public function getTuteurAddress(): ?string
    {
        return $this->tuteur_address;
    }

    public function setTuteurAddress(?string $tuteur_address): self
    {
        $this->tuteur_address = $tuteur_address;

        return $this;
    }

    public function getTuteurPhone(): ?string
    {
        return $this->tuteur_phone;
    }

    public function setTuteurPhone(?string $tuteur_phone): self
    {
        $this->tuteur_phone = $tuteur_phone;

        return $this;
    }

    public function getTuteurJob(): ?string
    {
        return $this->tuteur_job;
    }

    public function setTuteurJob(?string $tuteur_job): self
    {
        $this->tuteur_job = $tuteur_job;

        return $this;
    }

    public function getTuteurJobAddress(): ?string
    {
        return $this->tuteur_job_address;
    }

    public function setTuteurJobAddress(?string $tuteur_job_address): self
    {
        $this->tuteur_job_address = $tuteur_job_address;

        return $this;
    }

    public function getTuteurJobPhone(): ?string
    {
        return $this->tuteur_job_phone;
    }

    public function setTuteurJobPhone(?string $tuteur_job_phone): self
    {
        $this->tuteur_job_phone = $tuteur_job_phone;

        return $this;
    }

    public function getReligiousCongregationName(): ?string
    {
        return $this->religious_congregation_name;
    }

    public function setReligiousCongregationName(?string $religious_congregation_name): self
    {
        $this->religious_congregation_name = $religious_congregation_name;

        return $this;
    }

    public function getReligiousAddress(): ?string
    {
        return $this->religious_address;
    }

    public function setReligiousAddress(?string $religious_address): self
    {
        $this->religious_address = $religious_address;

        return $this;
    }

    public function getReligiousResponsableFoyerName(): ?string
    {
        return $this->religious_responsable_foyer_name;
    }

    public function setReligiousResponsableFoyerName(?string $religious_responsable_foyer_name): self
    {
        $this->religious_responsable_foyer_name = $religious_responsable_foyer_name;

        return $this;
    }

    public function getReligiousPhone(): ?string
    {
        return $this->religious_phone;
    }

    public function setReligiousPhone(?string $religious_phone): self
    {
        $this->religious_phone = $religious_phone;

        return $this;
    }

    public function getReligiousEmail(): ?string
    {
        return $this->religious_email;
    }

    public function setReligiousEmail(?string $religious_email): self
    {
        $this->religious_email = $religious_email;

        return $this;
    }

    public function getReligiousProfil(): ?string
    {
        return $this->religious_profil;
    }

    public function setReligiousProfil(?string $religious_profil): self
    {
        $this->religious_profil = $religious_profil;

        return $this;
    }

    public function getBaccSerie(): ?string
    {
        return $this->bacc_serie;
    }

    public function setBaccSerie(?string $bacc_serie): self
    {
        $this->bacc_serie = $bacc_serie;

        return $this;
    }

    public function getBaccAutreName(): ?string
    {
        return $this->bacc_autre_name;
    }

    public function setBaccAutreName(?string $bacc_autre_name): self
    {
        $this->bacc_autre_name = $bacc_autre_name;

        return $this;
    }

    public function getBaccAutreSerie(): ?string
    {
        return $this->bacc_autre_serie;
    }

    public function setBaccAutreSerie(?string $bacc_autre_serie): self
    {
        $this->bacc_autre_serie = $bacc_autre_serie;

        return $this;
    }

    public function getBaccMention(): ?string
    {
        return $this->bacc_mention;
    }

    public function setBaccMention(?string $bacc_mention): self
    {
        $this->bacc_mention = $bacc_mention;

        return $this;
    }

    public function getBaccSession(): ?string
    {
        return $this->bacc_session;
    }

    public function setBaccSession(?string $bacc_session): self
    {
        $this->bacc_session = $bacc_session;

        return $this;
    }

    public function getBaccNumInscription(): ?string
    {
        return $this->bacc_num_inscription;
    }

    public function setBaccNumInscription(?string $bacc_num_inscription): self
    {
        $this->bacc_num_inscription = $bacc_num_inscription;

        return $this;
    }

    public function getOriginEtablissement(): ?string
    {
        return $this->origin_etablissement;
    }

    public function setOriginEtablissement(?string $origin_etablissement): self
    {
        $this->origin_etablissement = $origin_etablissement;

        return $this;
    }

    public function getUniversityName(): ?string
    {
        return $this->university_name;
    }

    public function setUniversityName(?string $university_name): self
    {
        $this->university_name = $university_name;

        return $this;
    }

    public function getFaculteName(): ?string
    {
        return $this->faculte_name;
    }

    public function setFaculteName(?string $faculte_name): self
    {
        $this->faculte_name = $faculte_name;

        return $this;
    }

    public function getUniversityCountry(): ?string
    {
        return $this->university_country;
    }

    public function setUniversityCountry(?string $university_country): self
    {
        $this->university_country = $university_country;

        return $this;
    }

    public function getUniversityDiplome(): ?string
    {
        return $this->university_diplome;
    }

    public function setUniversityDiplome(?string $university_diplome): self
    {
        $this->university_diplome = $university_diplome;

        return $this;
    }

    public function getUniversityDiplomeDate(): ?\DateTimeInterface
    {
        return $this->university_diplome_date;
    }

    public function setUniversityDiplomeDate(?\DateTimeInterface $university_diplome_date): self
    {
        $this->university_diplome_date = $university_diplome_date;

        return $this;
    }

    public function getUniversityAutreTitre(): ?string
    {
        return $this->university_autre_titre;
    }

    public function setUniversityAutreTitre(?string $university_autre_titre): self
    {
        $this->university_autre_titre = $university_autre_titre;

        return $this;
    }

    public function getUniversityYear(): ?string
    {
        return $this->university_year;
    }

    public function setUniversityYear(?string $university_year): self
    {
        $this->university_year = $university_year;

        return $this;
    }

    public function getBaccAnnee(): ?int
    {
        return $this->bacc_annee;
    }

    public function setBaccAnnee(?int $bacc_annee): self
    {
        $this->bacc_annee = $bacc_annee;

        return $this;
    }

    public function getPayementRefPath(): ?string
    {
        return $this->payement_ref_path;
    }

    public function setPayementRefPath(?string $payement_ref_path): self
    {
        $this->payement_ref_path = $payement_ref_path;

        return $this;
    }

    public function getPayementRefDate(): ?\DateTimeInterface
    {
        return $this->payement_ref_date;
    }

    public function setPayementRefDate(\DateTimeInterface $payement_ref_date): self
    {
        $this->payement_ref_date = $payement_ref_date;

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

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire): self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

    public function getResultat(): ?int
    {
        return $this->resultat;
    }

    public function setResultat(?int $resultat): self
    {
        $this->resultat = $resultat;

        return $this;
    }

    public function getCentre(): ?ConcoursCentre
    {
        return $this->centre;
    }

    public function setCentre(?ConcoursCentre $centre): self
    {
        $this->centre = $centre;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    public function getEmailNotification(): ?bool
    {
        return $this->email_notification;
    }

    public function setEmailNotification(?bool $email_notification): self
    {
        $this->email_notification = $email_notification;

        return $this;
    }
}
