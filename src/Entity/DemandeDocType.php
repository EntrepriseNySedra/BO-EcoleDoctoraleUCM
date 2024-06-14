<?php

namespace App\Entity;

use App\Repository\DemandeDocTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeDocTypeRepository::class)
 */
class DemandeDocType
{
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
     * @ORM\OneToMany(targetEntity=DemandeDoc::class, mappedBy="type")
     */
    private $demandeDocs;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $code;

    /**
     * DemandeDocType constructor.
     */
    public function __construct()
    {
        $this->demandeDocs = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     *
     * @return \App\Entity\DemandeDocType
     */
    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection|DemandeDoc[]
     */
    public function getDemandeDocs(): Collection
    {
        return $this->demandeDocs;
    }

    /**
     * @param \App\Entity\DemandeDoc $demandeDoc
     *
     * @return \App\Entity\DemandeDocType
     */
    public function addDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if (!$this->demandeDocs->contains($demandeDoc)) {
            $this->demandeDocs[] = $demandeDoc;
            $demandeDoc->setType($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\DemandeDoc $demandeDoc
     *
     * @return \App\Entity\DemandeDocType
     */
    public function removeDemandeDoc(DemandeDoc $demandeDoc): self
    {
        if ($this->demandeDocs->removeElement($demandeDoc)) {
            // set the owning side to null (unless already changed)
            if ($demandeDoc->getType() === $this) {
                $demandeDoc->setType(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
