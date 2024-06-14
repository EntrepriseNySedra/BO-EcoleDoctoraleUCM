<?php

namespace App\Manager;

/**
 * Class UniteEnseignementsManager
 *
 * @package App\Manager
 */
class UniteEnseignementsManager extends BaseManager
{   
    /**
     * Get All  by mention
     * @param $mentionId
     * return ue[]
     */
    public function getAllByMention(int $mentionId) {
        $sql = "
        SELECT ue.* from unite_enseignements ue 
        inner join matiere mat on mat.unite_enseignements_id = ue.id

        WHERE ue.mention_id = :mentionId
        GROUP BY ue.id;         
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get All  by mention and enseignant
     * @param $mentionId, $enseignantId
     * return ue[]
     */
    public function getAllByMentionAndEnseignant(int $mentionId, int $enseignantId, int $niveauId, int $parcoursId = null) {
        $parcoursSqlFilter = $parcoursId ? " AND em.parcours_id = :parcoursId " : "";
        $sql = "
        SELECT e.first_name, e.last_name, em.mention_id, em.niveau_id, ue.id as ueId, ue.mention_id, ue.niveau_id, ue.libelle FROM enseignant e 
        INNER JOIN enseignant_mention em ON em.enseignant_id = e.id
        INNER JOIN unite_enseignements ue ON ue.mention_id = em.mention_id AND ue.niveau_id = em.niveau_id AND (ue.parcours_id = em.parcours_id OR ue.parcours_id IS NULL)
        WHERE em.mention_id = :mentionId
        AND em.enseignant_id = :enseignantId 
        AND em.niveau_id = :niveauId
        $parcoursSqlFilter ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get All  by criteria
     * @param mixed $tParams
     * return ue[]
     */
    public function findAllByCriteria($tParams) {
        $where = "WHERE id > 0";
        if(!empty($tParams['mentionId']))
            $where .= " AND mention_id = " . $tParams['mentionId'];
        if(!empty($tParams['parcoursId']))
            $where .= " AND parcours_id = " . $tParams['parcoursId'];
        if(!empty($tParams['niveauId']))
            $where .= " AND niveau_id = " . $tParams['niveauId'];
        if(!empty($tParams['semestreId']))
            $where .= " AND semestre_id = " . $tParams['semestreId'];

        $sql = "
        SELECT ue.* from unite_enseignements ue 
        $where
        ORDER BY libelle;         
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

}