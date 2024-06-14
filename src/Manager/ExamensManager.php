<?php

namespace App\Manager;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class ExamensManager
 *
 * @package App\Manager
 */
class ExamensManager extends BaseManager
{

    /**
     * @param int $etudiantId
     *
     * @return mixed
     */
    public function getByEtudiantId(int $etudiantId)
    {
        $sql = "
            SELECT
              e.id,
              e.libelle
            FROM
              `examens` e
              INNER JOIN notes n
                ON `e`.id = `n`.`examen_id`
            WHERE `n`.`etudiant_id` = " . $etudiantId . "
              AND e.deleted_at IS NULL
            GROUP BY e.id
        ";

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->class, 'e');
        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'libelle', 'libelle');

        $query = $this->em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
}