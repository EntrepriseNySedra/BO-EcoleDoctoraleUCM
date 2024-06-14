<?php

namespace App\Entity;

use App\Repository\CandidatureHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CandidatureHistoriqueRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class CandidatureHistorique
{

    /** @var array */
    static $statusText = ['Création', 'Validation', 'Annulation'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=ConcoursCandidature::class, inversedBy="candidatureHistoriques")
     * @ORM\JoinColumn(nullable=false)
     */
    private $candidature;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="candidatureHistoriques")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motif;

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
    public function getStatus() : ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return \App\Entity\CandidatureHistorique
     */
    public function setStatus(int $status) : self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \App\Entity\ConcoursCandidature|null
     */
    public function getCandidature() : ?ConcoursCandidature
    {
        return $this->candidature;
    }

    /**
     * @param \App\Entity\ConcoursCandidature|null $candidature
     *
     * @return \App\Entity\CandidatureHistorique
     */
    public function setCandidature(?ConcoursCandidature $candidature) : self
    {
        $this->candidature = $candidature;

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
     * @return \App\Entity\CandidatureHistorique
     */
    public function setUser(?User $user) : self
    {
        $this->user = $user;

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
     * @return \App\Entity\CandidatureHistorique
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
     * @return mixed
     */
    public function getStatusText()
    {
        return !empty(self::$statusText[$this->status]) ? self::$statusText[$this->status] : self::$statusText[0];
    }

    /**
     * @return string
     */
    public function getModerator()
    {
        return ($this->user instanceof User)
            ? $this->user->getLastName() . ' ' . $this->user->getFirstName() : '---';
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
}
