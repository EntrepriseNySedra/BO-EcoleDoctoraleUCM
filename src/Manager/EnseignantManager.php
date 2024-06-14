<?php

namespace App\Manager;

/**
 * Class EnseignantManager
 *
 * @package App\Manager
 */
class EnseignantManager extends BaseManager
{
    /**
     * Get All Enseignant filter by mention and niveau
     * @Param $tParams [mention, niveau]
     * return enseignant[]
     */
    public function getByMentionNiveau(array $tParams = [])
    {

        $query = " SELECT e.* FROM enseignant e 
            INNER JOIN enseignant_mention em ON em.enseignant_id = e.id
            WHERE em.mention_id = " . $tParams['mentionId'] .
                 " AND em.niveau_id = " . $tParams['niveauId'] .
                 " ORDER BY e.first_name ASC ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param int $mentionId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMention(int $mentionId = 0)
    {
        $query = "
            SELECT e.id, e.`first_name`, e.`last_name`, e.`email` 
            FROM `enseignant` e
            INNER JOIN `enseignant_mention` em ON e.id = `em`.`enseignant_id`
        ";
        if ($mentionId) {
            $query .= " WHERE `em`.`mention_id` = :mentionId ";
        }
        $query .= "
            GROUP BY `e`.id
            ORDER BY `e`.`first_name`
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($query);
        if ($mentionId) {
            $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_STR);
        }
        $statement->executeQuery();

        return $statement->fetchAll();
    }





    /**
     * @param string $keySearch
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function searchFromName($keySearch = '')
    {
        $query = "
            SELECT e.id, e.`last_name`, e.first_name
            FROM `enseignant` e
            WHERE `e`.`last_name` LIKE '" . $keySearch . "%'
        ";
        $query .= "
            ORDER BY `e`.`last_name`
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($query);
        // $statement->bindParam('keyValue', $keySearch, \PDO::PARAM_STR);
        $statement->executeQuery();

        return $statement->fetchAll();
    }





    /**
     * @param array $mentions
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMentions(array $mentions)
    {
        if ($mentions) {
            $query = "
                SELECT e.id, e.`first_name`, e.`last_name`, e.`email` 
                FROM `enseignant` e
                INNER JOIN `enseignant_mention` em ON e.id = `em`.`enseignant_id`
                WHERE `em`.`mention_id` IN (" . implode(',', $mentions) . " ) 
                GROUP BY `e`.id
                ORDER BY `e`.`first_name`
            ";

            $connection = $this->em->getConnection();
            $statement  = $connection->prepare($query);

            $statement->executeQuery();

            return $statement->fetchAll();
        }

        return [];
    }
}