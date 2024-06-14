<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Un utilisateur existe déjà avec cette adresse email.")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    /** @var int */
    const STATUS_ENABLED = 1;

    /** @var int */
    const STATUS_DISABLED = 0;

    /** @var array */
    static $statusList = [
        'Activé'    => self::STATUS_ENABLED,
        'Désactivé' => self::STATUS_DISABLED,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login;

    /**
     * @ORM\ManyToOne(targetEntity=Profil::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $profil;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConnectedAt;

    /**
     * @ORM\OneToMany(targetEntity=CandidatureHistorique::class, mappedBy="user")
     */
    private $candidatureHistoriques;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="users")
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class)
     */
    private $faculte;

    /**
     * @ORM\OneToMany(targetEntity=Prestation::class, mappedBy="user", orphanRemoval=true)
     */
    private $prestations;

    /**
     * @ORM\OneToMany(targetEntity=PaiementHistory::class, mappedBy="validator")
     */
    private $paiementHistories;

    /**
     * @ORM\OneToMany(targetEntity=CalendrierExamen::class, mappedBy="surveillant")
     */
    private $calendrierExamens;

    /**
     * @ORM\OneToMany(targetEntity=FraisScolarite::class, mappedBy="athor")
     */
    private $fraisScolarites;

    /**
     * @ORM\OneToMany(targetEntity=Concours::class, mappedBy="signataire")
     */
    private $concours;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fromImport = 0;

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
    private $tiers_num;

    /**
     * @ORM\OneToOne(targetEntity=Employe::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $employe;

    /**
     * @ORM\OneToMany(targetEntity=Discussion::class, mappedBy="auteur", orphanRemoval=true)
     */
    private $discussions;

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
    }

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->candidatureHistoriques = new ArrayCollection();
        $this->prestationUsers = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->paiementHistories = new ArrayCollection();
        $this->calendrierExamens = new ArrayCollection();
        $this->fraisScolarites = new ArrayCollection();
        $this->concours = new ArrayCollection();
        $this->employes = new ArrayCollection();
        $this->discussions = new ArrayCollection();
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
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return \App\Entity\User
     */
    public function setEmail(string $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername() : string
    {
        return (string) $this->email;
    }

    /**
     * @return string
     */
    public function getFullName() : string
    {
        return (string) $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @param string $roles
     *
     * @return \App\Entity\User
     */
    public function addRoles(string $roles) : self
    {
        if (!in_array($roles, $this->roles)) {
            $this->roles[] = $roles;
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles() : array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        foreach ($this->getProfil()->getRoles()->toArray() as $role) {
            $roles[] = $role->getCode();
        }

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return \App\Entity\User
     */
    public function setRoles(array $roles) : self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword() : string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     *
     * @return \App\Entity\User
     */
    public function setPassword(string $password) : self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt() : ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return bool
     */
    public function isVerified() : bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     *
     * @return \App\Entity\User
     */
    public function setIsVerified(bool $isVerified) : self
    {
        $this->isVerified = $isVerified;

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
     * @return \App\Entity\User
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
     * @return \App\Entity\User
     */
    public function setLastName(string $lastName) : self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogin() : ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return \App\Entity\User
     */
    public function setLogin(string $login) : self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return \App\Entity\Profil|null
     */
    public function getProfil() : ?Profil
    {
        return $this->profil;
    }

    /**
     * @param \App\Entity\Profil|null $profil
     *
     * @return \App\Entity\User
     */
    public function setProfil(?Profil $profil) : self
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getStatus() : ?bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     *
     * @return \App\Entity\User
     */
    public function setStatus(bool $status) : self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastConnectedAt() : ?\DateTimeInterface
    {
        return $this->lastConnectedAt;
    }

    /**
     * @param \DateTimeInterface|null $lastConnectedAt
     *
     * @return \App\Entity\User
     */
    public function setLastConnectedAt(?\DateTimeInterface $lastConnectedAt) : self
    {
        $this->lastConnectedAt = $lastConnectedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistEvent()
    {
        if (!$this->status) {
            $this->status = true;
        }
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
     * @return \App\Entity\User
     */
    public function addCandidatureHistorique(CandidatureHistorique $candidatureHistorique): self
    {
        if (!$this->candidatureHistoriques->contains($candidatureHistorique)) {
            $this->candidatureHistoriques[] = $candidatureHistorique;
            $candidatureHistorique->setUser($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\CandidatureHistorique $candidatureHistorique
     *
     * @return \App\Entity\User
     */
    public function removeCandidatureHistorique(CandidatureHistorique $candidatureHistorique): self
    {
        if ($this->candidatureHistoriques->removeElement($candidatureHistorique)) {
            // set the owning side to null (unless already changed)
            if ($candidatureHistorique->getUser() === $this) {
                $candidatureHistorique->setUser(null);
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
     * @return bool
     */
    public function isStudent()
    {
        return in_array('ROLE_ETUDIANT', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isTeacher()
    {
        return in_array('ROLE_ENSEIGNANT', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isAssistant()
    {
        return in_array('ROLE_ASSISTANT', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isMentionHead()
    {
        return in_array('ROLE_CHEFMENTION', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isDoyen()
    {
        return in_array('ROLE_DOYEN', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isSG()
    {
        return in_array('ROLE_SG', $this->getRoles());
    }

    /**
     * @return bool
     */
    public function isRector()
    {
        return in_array('ROLE_RECTEUR', $this->getRoles());
    }

    /**
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param string|null $confirmationToken
     *
     * @return \App\Entity\User
     */
    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

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

    public function getFaculte(): ?Departement
    {
        return $this->faculte;
    }

    public function setFaculte(?Departement $faculte): self
    {
        $this->faculte = $faculte;

        return $this;
    }

    // /**
    //  * @return Collection|PrestationUser[]
    //  */
    // public function getPrestationUsers(): Collection
    // {
    //     return $this->prestationUsers;
    // }

    // public function addPrestationUser(PrestationUser $prestationUser): self
    // {
    //     if (!$this->prestationUsers->contains($prestationUser)) {
    //         $this->prestationUsers[] = $prestationUser;
    //         $prestationUser->setUser($this);
    //     }

    //     return $this;
    // }

    // public function removePrestationUser(PrestationUser $prestationUser): self
    // {
    //     if ($this->prestationUsers->removeElement($prestationUser)) {
    //         // set the owning side to null (unless already changed)
    //         if ($prestationUser->getUser() === $this) {
    //             $prestationUser->setUser(null);
    //         }
    //     }

    //     return $this;
    // }

    /**
     * @return Collection|Prestation[]
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
            $prestation->setAuteur($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getAuteur() === $this) {
                $prestation->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PaiementHistory[]
     */
    public function getPaiementHistories(): Collection
    {
        return $this->paiementHistories;
    }

    public function addPaiementHistory(PaiementHistory $paiementHistory): self
    {
        if (!$this->paiementHistories->contains($paiementHistory)) {
            $this->paiementHistories[] = $paiementHistory;
            $paiementHistory->setValidator($this);
        }

        return $this;
    }

    public function removePaiementHistory(PaiementHistory $paiementHistory): self
    {
        if ($this->paiementHistories->removeElement($paiementHistory)) {
            // set the owning side to null (unless already changed)
            if ($paiementHistory->getValidator() === $this) {
                $paiementHistory->setValidator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CalendrierExamen[]
     */
    public function getCalendrierExamens(): Collection
    {
        return $this->calendrierExamens;
    }

    public function addCalendrierExamen(CalendrierExamen $calendrierExamen): self
    {
        if (!$this->calendrierExamens->contains($calendrierExamen)) {
            $this->calendrierExamens[] = $calendrierExamen;
            $calendrierExamen->setSurveillant($this);
        }

        return $this;
    }

    public function removeCalendrierExamen(CalendrierExamen $calendrierExamen): self
    {
        if ($this->calendrierExamens->removeElement($calendrierExamen)) {
            // set the owning side to null (unless already changed)
            if ($calendrierExamen->getSurveillant() === $this) {
                $calendrierExamen->setSurveillant(null);
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
            $fraisScolarite->setAthor($this);
        }

        return $this;
    }

    public function removeFraisScolarite(FraisScolarite $fraisScolarite): self
    {
        if ($this->fraisScolarites->removeElement($fraisScolarite)) {
            // set the owning side to null (unless already changed)
            if ($fraisScolarite->getAthor() === $this) {
                $fraisScolarite->setAthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Concours[]
     */
    public function getConcours(): Collection
    {
        return $this->concours;
    }

    public function addConcour(Concours $concour): self
    {
        if (!$this->concours->contains($concour)) {
            $this->concours[] = $concour;
            $concour->setSignataire($this);
        }

        return $this;
    }

    public function removeConcour(Concours $concour): self
    {
        if ($this->concours->removeElement($concour)) {
            // set the owning side to null (unless already changed)
            if ($concour->getSignataire() === $this) {
                $concour->setSignataire(null);
            }
        }

        return $this;
    }

    public function getFromImport(): ?bool
    {
        return $this->fromImport;
    }

    public function setFromImport(?bool $fromImport): self
    {
        $this->fromImport = $fromImport;

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

    public function getTiersNum(): ?string
    {
        return $this->tiers_num;
    }

    public function setTiersNum(?string $tiers_num): self
    {
        $this->tiers_num = $tiers_num;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employe): self
    {
        // unset the owning side of the relation if necessary
        if ($employe === null && $this->employe !== null) {
            $this->employe->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($employe !== null && $employe->getUser() !== $this) {
            $this->setFirstname($employe->getFirstName());
            $this->setLastname($employe->getLastName());
            $this->setEmail($employe->getEmail());
            $employe->setUser($this);
        }

        $this->employe = $employe;

        return $this;
    }

    /**
     * @return Collection<int, Discussion>
     */
    public function getDiscussions(): Collection
    {
        return $this->discussions;
    }

    public function addDiscussion(Discussion $discussion): self
    {
        if (!$this->discussions->contains($discussion)) {
            $this->discussions[] = $discussion;
            $discussion->setAuteur($this);
        }

        return $this;
    }

    public function removeDiscussion(Discussion $discussion): self
    {
        if ($this->discussions->removeElement($discussion)) {
            // set the owning side to null (unless already changed)
            if ($discussion->getAuteur() === $this) {
                $discussion->setAuteur(null);
            }
        }

        return $this;
    }
}
