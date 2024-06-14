<?php

namespace App\Manager;

/**
 * Class EvenementManager
 *
 * @package App\Manager
 */
class EvenementManager extends BaseManager
{
    /**
     * Get All available event
     * return events[]
     * */
    public function getAll() {
        $query = "SELECT *
            FROM evenement 
            WHERE date >= curdate()
            ORDER BY date DESC, title ASC
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();

    }
}