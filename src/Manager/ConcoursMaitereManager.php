<?php

namespace App\Manager;

/**
 * Description of ConcoursMaitereManager.php.
 *
 * @package App\Manager
 * @author Joelio
 */
class ConcoursMaitereManager extends BaseManager
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
        $qb->select('cm')
           ->from($this->class, 'cm')
           ->where('cm.deletedAt IS NULL')
        ;

        return $qb->getQuery()->getResult();

    }
}