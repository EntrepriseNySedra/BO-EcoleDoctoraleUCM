<?php

namespace App\Manager;

/**
 * Class ActualiteManager
 *
 * @package App\Manager
 */
class ActualiteManager extends BaseManager
{
    /**
     * Get All actualites with parent resource
     * return actus[]
     * @param $tParam array of SQL conditions, order, group, limit
     * */
    public function getAllWithParent() {
        $query = "SELECT a.*, (
            IF(rub.id IS NOT NULL, rub.title, IF(dep.id IS NOT NULL, dep.nom, IF(men.id IS NOT NULL, men.nom, \"\")))
            ) as resource_name
            FROM actualite a
            LEFT JOIN rubrique rub ON rub.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"RUBRIQUE\"
            LEFT JOIN departement dep ON dep.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"DEPARTEMENT\"
            LEFT JOIN mention men ON men.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"MENTION\"
            WHERE dep.id IS NOT NULL OR rub.id IS NOT NULL OR men.uuid IS NOT NULL
            ORDER BY a.updated_at ASC, a.created_at ASC";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();

    }

}