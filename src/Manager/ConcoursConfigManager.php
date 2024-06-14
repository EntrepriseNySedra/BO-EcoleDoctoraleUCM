<?php

namespace App\Manager;

/**
 * Class ConcoursConfigManager
 *
 * @package App\Manager
 */
class ConcoursConfigManager extends BaseManager
{
	/**
     * Get active concours
     * @return Concours[]
     */
    public function isActive()
    {
        $sql = "
            SELECT * FROM concours_config
            WHERE DATE_FORMAT(start_date, \"%Y-%m-%d\") < curdate() AND DATE_FORMAT(end_date, \"%Y-%m-%d\") > curdate()
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->executeQuery();

        return count($statement->fetchAll()) > 0 ? true : false;
    }
}