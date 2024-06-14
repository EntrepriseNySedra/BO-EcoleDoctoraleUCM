<?php

namespace App\Manager;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class DemandeDocManager
 *
 * @package App\Manager
 */
class DemandeDocManager extends BaseManager
{
    public function getDemandeDocByParams($params,$code) {
        $query = sprintf(
                "SELECT d.*, t.libelle FROM demande_doc d 
                INNER JOIN demande_doc_type t ON d.type_id = t.id  
                WHERE d.etudiant_id IN (%s)  AND d.statut != ''
                AND t.code = '%s'  ORDER BY d.id ASC 
                ", $params, $code);
    
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    public function getDemandeDocTypeDoc() {
        $query = sprintf(
            "SELECT d.*, t.libelle FROM demande_doc d 
                INNER JOIN demande_doc_type t ON d.type_id = t.id  
                WHERE d.statut != '' AND t.code !='LETTRE_INTRODUCTION' 
                "
        );

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}