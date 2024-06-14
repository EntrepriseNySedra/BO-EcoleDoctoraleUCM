<?php

namespace App\Manager;

/**
 * Class CandidatureHistoriqueManager
 *
 * @package App\Manager
 */
class CandidatureHistoriqueManager extends BaseManager
{

    /**
     * @param array $criteria
     * @param null  $order
     *
     * @return mixed
     */
    public function findByCriteria(array $criteria = [], $order = null)
    {
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('ch')
           ->from($this->class, 'ch')
        ;

        return $qb->getQuery()->getResult();

    }
}