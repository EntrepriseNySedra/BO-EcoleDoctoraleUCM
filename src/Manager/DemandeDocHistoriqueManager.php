<?php

namespace App\Manager;

/**
 * Class DemandeDocHistoriqueManager
 *
 * @package App\Manager
 */
class DemandeDocHistoriqueManager extends BaseManager
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
        $qb->select('dh')
           ->from($this->class, 'dh')
        ;

        return $qb->getQuery()->getResult();

    }
}