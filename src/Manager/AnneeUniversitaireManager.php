<?php

namespace App\Manager;

/**
 * Class AnneeUniversitaireManager
 *
 * @package App\Manager
 */
class AnneeUniversitaireManager extends BaseManager
{
    /**
     * Get current
     * return AnneeUniversitaire
     * */
    public function getCurrent() {
        $currentDate = date('Y-m-d');
        $currenYear  = date('Y');
        $query = "SELECT au.*
            FROM annee_universitaire au
            WHERE (DATE_FORMAT(au.date_debut, '%Y-%m-%d') <= :curDate
            AND DATE_FORMAT(au.date_fin, '%Y-%m-%d') >= :curDate)
            OR (DATE_FORMAT(au.date_debut, '%Y') = :curYear
            OR DATE_FORMAT(au.date_fin, '%Y') = :curYear)
            ORDER BY libelle DESC";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('curDate', $currentDate, \PDO::PARAM_STR);
        $statement->bindParam('curYear', $currenYear, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetch();
    }

}