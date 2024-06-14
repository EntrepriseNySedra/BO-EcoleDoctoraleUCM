<?php

namespace App\Manager;

use App\Entity\Concours;
use App\Entity\Mention;

/**
 * Class ConcoursEmploiDuTempsManager
 *
 * @package App\Manager
 */
class ConcoursEmploiDuTempsManager extends BaseManager
{

    /**
     * @param array $criterias
     * @param null  $order
     *
     * @return mixed
     */
    public function findByCriteria(array $criterias = [], $order = null)
    {
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('cedt')
           ->from($this->class, 'cedt')
        ;

        foreach ($criterias as $key => $criteria) {
            $qb->andWhere('cedt.' . $key . ' = :' . $key)->setParameter($key, $criteria);
        }

        return $qb->getQuery()->getResult();

    }

    /**
     * @param int $concoursId
     * @param int $mentionId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByConcoursIdAndMentionId(int $concoursId, int $mentionId)
    {
        $sql = "
            SELECT `cm`.`libelle` matiere, `s`.`libelle` salle, `cedt`.`start_date` startDate, `cedt`.end_date endDate FROM `concours_emploi_du_temps` cedt
            INNER JOIN `concours` c ON `c`.`id` = `cedt`.`concours_id`
            INNER JOIN `concours_matiere` cm ON `cm`.`id` = `cedt`.`concours_matiere_id`
            INNER JOIN `salles` s ON `s`.`id` = `cedt`.`salle_id`
            WHERE `c`.`id` = :concoursId
            AND `cm`.`mention_id` = :mentionId
            ORDER BY `cm`.`libelle` ASC
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('concoursId', $concoursId, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}
