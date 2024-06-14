<?php

namespace App\Manager;

/**
 * Class EnseignantMentionManager
 *
 * @package App\Manager
 */
class EnseignantMentionManager extends BaseManager
{
    /**
     * Get All  by mention
     * @param $mentionId
     * return enseignant[]
     */
    public function getParcourables(int $enseignantId, int $mentionId) {
        // $sql = "
        //     SELECT DISTINCT em.parcours_id as parcoursId, p.* FROM enseignant e INNER JOIN enseignant_mention em ON em.enseignant_id = e.id
        //     INNER JOIN niveau n on n.id = em.niveau_id
        //     INNER JOIN parcours p on p.niveau_id = n.id
        //     WHERE e.id = :pEnseignantId
        //     AND p.mention_id = :pMentionId
        //     ;
         
        // ";
        $sql = "
            -- SELECT DISTINCT em.parcours_id as parcoursId, p.* FROM enseignant_mention em 
            -- INNER JOIN enseignant e ON e.id = em.enseignant_id
            -- INNER JOIN niveau n on n.id = em.niveau_id
            -- INNER JOIN parcours p on p.niveau_id = em.niveau_id
            -- WHERE em.enseignant_id = :pEnseignantId
            -- AND em.mention_id = :pMentionId
            SELECT em.parcours_id as parcoursId, p.* FROM parcours p 
            INNER JOIN enseignant_mention em ON em.mention_id = p.mention_id AND em.niveau_id = p.niveau_id
            WHERE em.enseignant_id = :pEnseignantId
            AND em.mention_id = :pMentionId ";        

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pEnseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->bindParam('pMentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get All  by mention
     * @param $mentionId
     * return enseignant[]
     */
    public function getAllByMention(int $mentionId) {
        $sql = "
        SELECT ens.*, em.mention_id FROM enseignant ens 
        INNER JOIN enseignant_mention em on em.enseignant_id = ens.id
        
        WHERE em.mention_id = :mentionId
        AND ens.status = 1
        GROUP BY ens.id
        ORDER BY ens.last_name ASC, ens.first_name ASC;
         
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get All  by mixed params
     * @param $tParams
     * return mixed[]
     */
    public function getAllByParams(array $tParams) {
        $sqlAndWhere = "";
        $bindParamParcours = false;
        if(array_key_exists('parcours', $tParams)) {
            $sqlAndWhere .= " AND parcours_id = :parcoursId";
            $bindParamParcours = true;
        }
        $sql = 
            sprintf("SELECT ens.*, em.mention_id FROM enseignant ens 
            INNER JOIN enseignant_mention em on em.enseignant_id = ens.id
            WHERE em.mention_id = :mentionId
            AND em.niveau_id = :niveauId
            %s
            GROUP BY ens.id
            ORDER BY ens.first_name ASC;
            ", $sqlAndWhere);       

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $tParams['mention'], \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $tParams['niveau'], \PDO::PARAM_INT);
        if($bindParamParcours)
        $statement->bindParam('parcoursId', $tParams['parcours'], \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }
}