<?php

namespace App\Services;

use App\Manager\AnneeUniversitaireManager;

/**
 * Description of AnneeUniversitaireService.php.
 *
 * @package App\Services
 */
class AnneeUniversitaireService
{

    /**
     * @var \App\Manager\AnneeUniversitaireManager
     */
    private $anneeUniversitaireManager;

    /**
     * AnneeUniversitaireService constructor.
     *
     * @param \App\Manager\AnneeUniversitaireManager $anneeUniversitaireManager
     */
    public function __construct(AnneeUniversitaireManager $anneeUniversitaireManager)
    {
        $this->anneeUniversitaireManager = $anneeUniversitaireManager;
    }

    /**
     * @param int $year
     *
     * @return object|null
     */
    public function checkByYear($year)
    {
        return $this->anneeUniversitaireManager->loadOneBy(
            [
                'annee' => $year
            ]
        );
    }

    public function check()
    {
        $current = $this->anneeUniversitaireManager->getCurrent();
        dd($current);
    }

    /**
     * @param int $currentYear
     *
     * @return \App\Entity\AnneeUniversitaire
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createByYear(int $currentYear)
    {
        $libelle = date('Y') . '-' . $currentYear;

        /** @var \App\Entity\AnneeUniversitaire $object */
        $object = $this->anneeUniversitaireManager->createObject();
        $object->setLibelle($libelle);
        $object->setAnnee($currentYear);        

        $this->anneeUniversitaireManager->save($object);

        return $object;
    }

    /**
     * @return \App\Entity\AnneeUniversitaire|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getCurrent()
    {
        $current = $this->anneeUniversitaireManager->getCurrent();                
        return $current ? $this->anneeUniversitaireManager->load($current['id']) : null;
    }
}