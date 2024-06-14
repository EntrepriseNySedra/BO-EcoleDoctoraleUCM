<?php

namespace App\Manager;

/**
 * Class ArticleManager
 *
 * @package App\Manager
 */
class ArticleManager extends BaseManager
{
    /**
     * Get All articles with parent resource
     * return article[]
     * @param $tParam array of SQL conditions, order, group, limit
     * */
    public function getAllWithParent() {
        $query = "SELECT a.*, (
            IF(rub.id IS NOT NULL, rub.title, IF(dep.id IS NOT NULL, dep.nom, IF(men.id IS NOT NULL, men.nom, \"\")))
            ) as resource_name
            FROM article a
            LEFT JOIN rubrique rub ON rub.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"RUBRIQUE\"
            LEFT JOIN departement dep ON dep.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"DEPARTEMENT\"
            LEFT JOIN mention men ON men.uuid = a.resource_uuid AND UPPER(a.ressource_type) = \"MENTION\"
            -- WHERE dep.id IS NOT NULL OR rub.id IS NOT NULL OR men.uuid IS NOT NULL
            ORDER BY a.updated_at ASC, a.created_at ASC";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();

    }

    /**
     * Get Article filter by location
     * return article []
     * @param location
     */
    public function getArticle($location) {
        $query = "SELECT * FROM article";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

}