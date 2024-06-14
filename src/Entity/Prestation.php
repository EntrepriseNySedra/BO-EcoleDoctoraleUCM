<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrestationRepository::class)
 */
class Prestation
{
    const STATUS_CREATED            = 1;
    const STATUS_ASSIST_VALIDATED   = 2;
    const STATUS_CM_VALIDATED       = 3;
    const STATUS_COMPTA_VALIDATED   = 4;
    const STATUS_RF_VALIDATED       = 5;
    const STATUS_SG_VALIDATED       = 6;
    const STATUS_RECTEUR_VALIDATED  = 7;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=TypePrestation::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type_prestation;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $annee_universitaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    public function __construct()
    {
        // $this->prestationUsers = new ArrayCollection();
        // $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    // /**
    //  * @return Collection|User[]
    //  */
    // public function getUsers(): Collection
    // {
    //     return $this->users;
    // }

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
    //         $prestationUser->setPrestation($this);
    //     }

    //     return $this;
    // }

    // public function removePrestationUser(PrestationUser $prestationUser): self
    // {
    //     if ($this->prestationUsers->removeElement($prestationUser)) {
    //         // set the owning side to null (unless already changed)
    //         if ($prestationUser->getPrestation() === $this) {
    //             $prestationUser->setPrestation(null);
    //         }
    //     }

    //     return $this;
    // }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->annee_universitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $annee_universitaire): self
    {
        $this->annee_universitaire = $annee_universitaire;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
}
