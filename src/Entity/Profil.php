<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfilRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Profil
{

    /**
     *
     */
    const ETUDIANT                  = 'Etudiant';
    const ENSEIGNANT                = 'Enseignant';
    const SURVEILLANT               = 'Surveillant';
    const CHEF_MENTION              = 'Chef mention';
    const ENSEIGNANT_TPL_NAME       = 'Teacher';
    /**
     *
     */
    const STATUS_ENABLED    = 'ENABLED';
    const STATUS_DISABLED = 'DISABLED';

    /**
     * @var array
     */
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Roles::class)
     * @ORM\JoinTable(name="profil_has_roles")
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="profil")
     */
    private $users;

    /**
     * Profil constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->users = new ArrayCollection();
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
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \App\Entity\Profil
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus() : ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return \App\Entity\Profil
     */
    public function setStatus(string $status) : self
    {
        $this->status = $status;

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
     * @return \App\Entity\Profil
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
     * @return \App\Entity\Profil
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Roles[]
     */
    public function getRoles() : Collection
    {
        return $this->roles;
    }

    /**
     * @param \App\Entity\Roles $role
     *
     * @return \App\Entity\Profil
     */
    public function addRole(Roles $role) : self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param \App\Entity\Roles $role
     *
     * @return \App\Entity\Profil
     */
    public function removeRole(Roles $role) : self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return false|int|string
     */
    public function getFormatedStatus()
    {
        return array_search($this->status, self::$statusList);
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
        $this->createdAt = !$this->createdAt ? new \DateTime() : $this->createdAt;
    }

    /**
     * @throws \Exception
     */
    private function setUpdatedAtValue()
    {
        $this->updatedAt = !$this->updatedAt ? new \DateTime() : $this->updatedAt;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return \App\Entity\Profil
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setProfil($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return \App\Entity\Profil
     */
    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getProfil() === $this) {
                $user->setProfil(null);
            }
        }

        return $this;
    }
}
