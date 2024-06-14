<?php

namespace App\Manager;

/**
 * Class FichePresenceEnseignantHistoriqueManager
 *
 * @package App\Manager
 */
class FichePresenceEnseignantHistoriqueManager extends BaseManager
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
        $qb->select('fh')
           ->from($this->class, 'fh')
        ;

        return $qb->getQuery()->getResult();

    }
}