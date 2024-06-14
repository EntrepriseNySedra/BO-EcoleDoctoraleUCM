<?php

namespace App\Entity;

use App\Repository\ConcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ConcoursRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Concours
{

    /**
     *
     */
    const STATUS_CREATED = 1;
    const STATUS_VALIDATED_CM = 2;
    const STATUS_VALIDATED_DOYEN = 3;
    const STATUS_VALIDATED_SG = 4;
    const STATUS_VALIDATED_RECTEUR = 5;
    const DEFAULT_DELIBERATION = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true,)
     */
    private $deliberation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="Concours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursMatiere::class, mappedBy="Concours")
     */
    private $concoursMatieres;

    /**
     * @ORM\OneToMany(targetEntity=ConcoursEmploiDuTemps::class, mappedBy="concours")
     */
    private $concoursEmploiDuTemps;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class)
     */
    private $parcours;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $result_statut;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="concours")
     */
    private $signataire;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class, inversedBy="concours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $annee_universitaire;

    public function __construct()
    {
        $this->concoursMatieres = new ArrayCollection();
        $this->concoursEmploiDuTemps = new ArrayCollection();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // $metadata->addPropertyConstraint('annee_universitaire', new Assert\NotNull([
        //         'message' => 'Veuillez saisir l\'année universitaire'
        //     ])
        // );
        $metadata->addConstraint(
            new UniqueEntity([
                'fields' => ['mention', 'niveau', 'parcours'],
                'ignoreNull' => false,
                'errorPath' => 'mention',
                'message' => 'Concours déjà en base'
            ])
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDeliberation(): ?string
    {
        return $this->deliberation;
    }

    public function setDeliberation(string $deliberation): self
    {
        $this->deliberation = $deliberation;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

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

    /**
     * @return Collection|ConcoursMatiere[]
     */
    public function getConcoursMatieres(): Collection
    {
        return $this->concoursMatieres;
    }

    public function addConcoursMatiere(ConcoursMatiere $concoursMatiere): self
    {
        if (!$this->concoursMatieres->contains($concoursMatiere)) {
            $this->concoursMatieres[] = $concoursMatiere;
            $concoursMatiere->setConcours($this);
        }

        return $this;
    }

    public function removeConcoursMatiere(ConcoursMatiere $concoursMatiere): self
    {
        if ($this->concoursMatieres->removeElement($concoursMatiere)) {
            // set the owning side to null (unless already changed)
            if ($concoursMatiere->getConcours() === $this) {
                $concoursMatiere->setConcours(null);
            }
        }

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
     * @return mixed
     */
    public function getFormatedStartDate()
    {
        return $this->startDate->format('d/m/Y');
    }

    /**
     * @return mixed
     */
    public function getFormatedEndDate()
    {
        return $this->endDate->format('d/m/Y');
    }

    /**
     * @return Collection|ConcoursEmploiDuTemps[]
     */
    public function getConcoursEmploiDuTemps(): Collection
    {
        return $this->concoursEmploiDuTemps;
    }

    public function addConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if (!$this->concoursEmploiDuTemps->contains($concoursEmploiDuTemp)) {
            $this->concoursEmploiDuTemps[] = $concoursEmploiDuTemp;
            $concoursEmploiDuTemp->setConcours($this);
        }

        return $this;
    }

    public function removeConcoursEmploiDuTemp(ConcoursEmploiDuTemps $concoursEmploiDuTemp): self
    {
        if ($this->concoursEmploiDuTemps->removeElement($concoursEmploiDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($concoursEmploiDuTemp->getConcours() === $this) {
                $concoursEmploiDuTemp->setConcours(null);
            }
        }

        return $this;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $Mention): self
    {
        $this->mention = $Mention;

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

    public function getResultStatut(): ?string
    {
        return $this->result_statut;
    }

    public function setResultStatut(?string $result_statut): self
    {
        $this->result_statut = $result_statut;

        return $this;
    }

    public function getSignataire(): ?User
    {
        return $this->signataire;
    }

    public function setSignataire(?User $signataire): self
    {
        $this->signataire = $signataire;

        return $this;
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->annee_universitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $annee_universitaire): self
    {
        $this->annee_universitaire = $annee_universitaire;

        return $this;
    }
}
