<?php

namespace App\Manager;

/**
 * Class ConcoursManager
 *
 * @package App\Manager
 * @author Joelio
 */
class ConcoursManager extends BaseManager
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
        $qb->select('c')
           ->from($this->class, 'c')
           ->where('c.deletedAt IS NULL')
        ;

        return $qb->getQuery()->getResult();

    }

    /**
     * Get active concours
     * @return Concours[]
     */
    public function findActive()
    {
        $sql = "
            SELECT * FROM concours
            WHERE DATE_FORMAT(start_date, \"%Y-%m-%d\") < curdate() AND DATE_FORMAT(end_date, \"%Y-%m-%d\") > curdate()
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get active concours
     * @return Concours[]
     */
    public function findByCandidateMentionNiveau($_mentionId, $_niveauId)
    {
        $sql = "
            SELECT * FROM concours
            WHERE DATE_FORMAT(start_date, \"%Y-%m-%d\") < curdate() AND DATE_FORMAT(end_date, \"%Y-%m-%d\") > curdate()
            AND mention_id = :mention
            AND niveau_id = :niveau
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mention', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveau', $_niveauId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get active concours
     * @return Concours[]
     */
    public function findByMentionActive($_mentionId)
    {
        $sql = "
            SELECT * FROM concours
            WHERE DATE_FORMAT(start_date, \"%Y-%m-%d\") < curdate() AND DATE_FORMAT(end_date, \"%Y-%m-%d\") > curdate()
            AND mention_id = :mention
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mention', $_mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

}