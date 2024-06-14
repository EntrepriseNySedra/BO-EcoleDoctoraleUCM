<?php

namespace App\Entity;

use App\Repository\FraisScolariteRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=FraisScolariteRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class FraisScolarite
{

    const STATUS_CREATED = 1;
    const STATUS_SRS_VALIDATED = 2;
    const STATUS_SRS_REFUSED = -2;
    const STATUS_COMPTA_VALIDATED = 3; 

    const MODE_PAIEMENT_VIREMENT = 1;//Virement
    const MODE_PAIEMENT_AGENCE = 2;//Chèque
    const MODE_PAIEMENT_CAISSE = 3;//Espèces
    const MODE_PAIEMENT_AUTRE = 4;//Autre

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class, inversedBy="fraisScolarites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=Mention::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveau;

    /**
     * @ORM\ManyToOne(targetEntity=Parcours::class)
     */
    private $parcours;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reste;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_paiement;

    /**
     * @ORM\ManyToOne(targetEntity=Semestre::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $semestre;

    /**
     * @ORM\ManyToOne(targetEntity=AnneeUniversitaire::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $annee_universitaire;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mode_paiement;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remitter;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="fraisScolarites")
     */
    private $author;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentRef;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentRefPath;

    /**
     * @ORM\OneToOne(targetEntity=Inscription::class, mappedBy="frais_scolarite", cascade={"persist", "remove"})
     */
    private $inscription;


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(
            new UniqueEntity([
                'fields' => ['etudiant', 'reference'],
                'ignoreNull' => false,
                'errorPath' => 'reference',
                'message' => 'Déjà enregistré'
            ])
        );
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getReste(): ?float
    {
        return $this->reste;
    }

    public function setReste(?float $reste): self
    {
        $this->reste = $reste;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->date_paiement;
    }

    public function setDatePaiement(\DateTimeInterface $date_paiement): self
    {
        $this->date_paiement = $date_paiement;

        return $this;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

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

    public function getModePaiement(): ?int
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(?int $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;

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

    public function getRemitter(): ?string
    {
        return $this->remitter;
    }

    public function setRemitter(?string $remitter): self
    {
        $this->remitter = $remitter;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
    private function setCreatedAtValue() {
        $this->created_at = !$this->created_at ? $this->created_at = new \DateTime() : $this->created_at ;
    }

    /**
     * @throws \Exception 
     */
    private function setUpdatedAtValue() {
        $this->updated_at = !$this->updated_at ? $this->updated_at = new \DateTime() : $this->updated_at ;
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

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPaymentRef(): ?string
    {
        return $this->paymentRef;
    }

    public function setPaymentRef(?string $paymentRef): self
    {
        $this->paymentRef = $paymentRef;

        return $this;
    }

    public function getPaymentRefPath(): ?string
    {
        return $this->paymentRefPath;
    }

    public function setPaymentRefPath(?string $paymentRefPath): self
    {
        $this->paymentRefPath = $paymentRefPath;

        return $this;
    }

    public function getInscription(): ?Inscription
    {
        return $this->inscription;
    }

    public function setInscription(?Inscription $inscription): self
    {
        // unset the owning side of the relation if necessary
        if ($inscription === null && $this->inscription !== null) {
            $this->inscription->setFraisScolarite(null);
        }

        // set the owning side of the relation if necessary
        if ($inscription !== null && $inscription->getFraisScolarite() !== $this) {
            $inscription->setFraisScolarite($this);
        }

        $this->inscription = $inscription;

        return $this;
    }
}
