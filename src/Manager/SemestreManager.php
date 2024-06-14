<?php

namespace App\Manager;

/**
 * Class SemestreManager
 *
 * @package App\Manager
 */
class SemestreManager extends BaseManager
{

    /**
     * @param $niveauId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCurrentSemester(int $niveauId)
    {
        $qb = $this->em->createQueryBuilder()
                ->select('s')
                ->from($this->class, 's')
                ->where('s.startDate <= :now')
                ->andWhere('s.endDate >= :now ')
                ->andWhere('s.niveau = :niveauId')
                ->setParameters(array('now' => new \DateTime(), 'niveauId' => $niveauId ))        
        ;      
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}