<?php

namespace App\Manager;

use App\Entity\Inscription;

/**
 * Class InscriptionManager
 *
 * @package App\Manager
 */
class InscriptionManager extends BaseManager
{

    /**
     * @param int $studentId
     * @param int $collegeYear
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByStudentIdAndCollegeYear(int $studentId, int $collegeYearId)
    {
        $sql = "
            SELECT `i`.`status` FROM `inscription` i
            INNER JOIN `annee_universitaire` au ON `au`.`id` = `i`.`annee_universitaire_id`
            WHERE `i`.`etudiant_id` = :studentId
            AND `au`.`id` = :collegeYearId
            ORDER BY `i`.`payement_ref_date` DESC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('studentId', $studentId, \PDO::PARAM_INT);
        $statement->bindParam('collegeYearId', $collegeYearId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchOne();
    }

    /**
     * @param int $collegeYearId
     * @param int $mentionId
     * @param int $niveauId
     *
     * @return false|mixed
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByCollegeYearAndMentionAndLevel(int $collegeYearId, int $mentionId, int $niveauId, int $parcoursId)
    {
        $queryParcoursFilter = $parcoursId  ? " AND `p`.`id`= :parcoursId " : "";
        $sql = "
            SELECT e.*, `m`.`nom` mention, `n`.`libelle` as niveau, `p`.`nom` as parcours, `c`.`libelle` as civility FROM `inscription` i
            INNER JOIN `etudiant` e ON `e`.`id` = `i`.`etudiant_id`
            LEFT JOIN `civility` c ON `c`.`id` = `e`.`civility_id`
            INNER JOIN `mention` m ON `m`.`id` = `e`.`mention_id`
            INNER JOIN `niveau` n ON `n`.`id` = `e`.`niveau_id`
            LEFT JOIN `parcours` p ON `p`.`id` = `e`.`parcours_id`
            WHERE `i`.`annee_universitaire_id` = :collegeYearId
            AND `i`.`status` = :status
            AND `m`.`id` = :mentionId 
            AND `n`.`id`= :niveauId
            $queryParcoursFilter
            GROUP BY e.id
            ORDER BY SUBSTR(e.immatricule, -4, 4) ASC
        ";

        $status = Inscription::STATUS_VALIDATED;

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('collegeYearId', $collegeYearId, \PDO::PARAM_INT);
        $statement->bindParam('status', $status, \PDO::PARAM_STR);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}