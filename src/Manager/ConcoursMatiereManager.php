<?php

namespace App\Manager;

/**
 * Class ConcoursMatiereManager
 *
 * @package App\Manager
 * @author Joelio
 */
class ConcoursMatiereManager extends BaseManager
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
        if (isset($criteria['mention'])) {
            $qb->andWhere('cm.mention = :mention')
               ->setParameter('mention', $criteria['mention'])
            ;
        }
        if (isset($criteria['concours'])) {
            $qb->andWhere('cm.concours = :concours')
               ->setParameter('concours', $criteria['concours'])
            ;
        }
        if (isset($criteria['parcours'])) {
            if ($criteria['parcours']) {
                $qb->andWhere('cm.parcours = :parcours')
                   ->setParameter('parcours', $criteria['parcours'])
                ;
            } else {
                $qb->andWhere('cm.parcours IS NULL');
            }
        }

        if (is_array($order)) {
            foreach ($order as $sort => $direction) {
                $qb->addOrderBy('cm.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();

    }
}