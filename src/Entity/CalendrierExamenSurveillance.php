<?php

namespace App\Entity;

use App\Repository\CalendrierExamenSurveillanceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalendrierExamenSurveillanceRepository::class)
 */
class CalendrierExamenSurveillance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $surveillant;

    /**
     * @ORM\ManyToOne(targetEntity=CalendrierExamen::class, inversedBy="calendrierExamenSurveillances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $calendrier_examen;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveillant(): ?User
    {
        return $this->surveillant;
    }

    public function setSurveillant(?User $surveillant): self
    {
        $this->surveillant = $surveillant;

        return $this;
    }

    public function getCalendrierExamen(): ?CalendrierExamen
    {
        return $this->calendrier_examen;
    }

    public function setCalendrierExamen(?CalendrierExamen $calendrier_examen): self
    {
        $this->calendrier_examen = $calendrier_examen;

        return $this;
    }
}
