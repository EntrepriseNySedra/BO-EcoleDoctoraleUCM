<?php

namespace App\Manager;

/**
 * Class EtudiantManager
 *
 * @package App\Manager
 */
class SallesManager extends BaseManager
{
    public function getSallesByParams($params) {
        $query = "  SELECT s.*, b.* FROM salles s  INNER JOIN batiment b ON b.id = s.batiment_id "
                    . "WHERE s.id NOT IN ('" . $params . "') "
                    . "ORDER BY s.id ASC ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get all salles filter by mixed params
     * @return mixed data 
     */
    public function getAllByMixedFilters($params) {
        if(isset($params['troncCommun'])) {
            $ensEdtTroncCommunEdt = $this->getEnseignantTroncCommunEdt($params);
            if(count($ensEdtTroncCommunEdt))
                return $ensEdtTroncCommunEdt;
        } 
        $countEnsEdtInCurrentSchedule = $this->checkEnseignantEdtByInterval($params);

        //dump($countEnsEdtInCurrentSchedule);die;

        if($countEnsEdtInCurrentSchedule > 0)
            return [];
    
        //start sql
        // dump($params);die;
        $query = "  
            SELECT DISTINCT s.id, b.nom as batimentNom, s.libelle, s.capacite, s.internet_connexion_on, s.video_projecteur_on, edt.id as edtId, edt.date_schedule, edt.start_time, edt.end_time, m.nom as matiere, edt.tronc_commun
            FROM salles s 
            LEFT JOIN emploi_du_temps edt on edt.salles_id = s.id
            LEFT JOIN batiment b ON b.id = s.batiment_id
            LEFT JOIN matiere m ON m.id = edt.matiere_id 
            WHERE              
            (s.id NOT IN 
                (
                    SELECT salles_id FROM emploi_du_temps edt
                    INNER JOIN matiere m ON m.id = edt.matiere_id
                    WHERE date_schedule = :pDateSchedule 
                    AND  (
                        (start_time > :pStartTime AND start_time < :pEndTime) OR (end_time > :pStartTime AND end_time < :pEndTime)
                        OR
                        (start_time < :pEndTime) AND (end_time > :pStartTime)
                    )
                )
            )
            AND s.capacite >= :pCapacite
        ";
        $query .= $params['connexion'] ? " AND s.internet_connexion_on = :connecionOn " : "" ;
        $query .= $params['videoProjecteur'] ? " AND s.video_projecteur_on = :videoProjecteurOn " : "" ;
        $query .= "    GROUP BY s.id";
        //end sql
        // dump($query);die;
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);

        $statement->bindParam('pDateSchedule',  $params['date'], \PDO::PARAM_STR);
        $statement->bindParam('pEndTime',   $params['endTime'], \PDO::PARAM_STR);
        $statement->bindParam('pStartTime', $params['startTime'], \PDO::PARAM_STR);
        $statement->bindParam('pCapacite',  $params['capacite'], \PDO::PARAM_INT);
        if($params['connexion'])
            $statement->bindParam('connecionOn',  $params['connexion'], \PDO::PARAM_INT);
        if($params['videoProjecteur'])
            $statement->bindParam('videoProjecteurOn',  $params['videoProjecteur'], \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }



    /**
     * Check if Teacher NOT common core EDT exist at given interval
     * @params mixed
     * @return count item
     * */

    private function checkEnseignantEdtByInterval($params) {
        $query = " 
            SELECT edt.salles_id FROM emploi_du_temps edt
            INNER JOIN matiere m ON m.id = edt.matiere_id
            WHERE edt.date_schedule = :pDateSchedule
            AND (
                    -- (edt.start_time >= :pStartTime AND edt.start_time <= :pEndTime) 
                    -- OR (edt.end_time >= :pStartTime AND edt.end_time <= :pEndTime)
                    (edt.start_time > :pStartTime AND edt.start_time < :pEndTime) 
                    OR (edt.end_time > :pStartTime AND edt.end_time < :pEndTime)
                ) 
            AND m.enseignant_id = :pEnseignantId
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pDateSchedule',  $params['date'], \PDO::PARAM_STR);
        $statement->bindParam('pEndTime',   $params['endTime'], \PDO::PARAM_STR);
        $statement->bindParam('pStartTime', $params['startTime'], \PDO::PARAM_STR);
        $statement->bindParam('pEnseignantId',  $params['enseignant'], \PDO::PARAM_INT);    
        $statement->executeQuery();
        
        return ($result = $statement->fetchAll()) ? count($result) : 0;
    }    

    /**
     * Check if Teacher NOT common core EDT exist at given interval
     * @params mixed
     * @return count item
     * */

    private function getEnseignantTroncCommunEdt($params) {
        // dump($params);die;
        $query = " 
            SELECT DISTINCT s.id, b.nom as batimentNom, s.libelle, s.capacite, s.internet_connexion_on, s.video_projecteur_on, edt.id as edtId, edt.date_schedule, edt.start_time, edt.end_time, m.nom as matiere, edt.tronc_commun FROM emploi_du_temps edt
            INNER JOIN salles s ON s.id = edt.salles_id
            INNER JOIN batiment b ON b.id = s.batiment_id
            INNER JOIN matiere m ON m.id = edt.matiere_id
            WHERE edt.tronc_commun  = 1
            AND edt.date_schedule   = :pDateSchedule
            AND (
                    (edt.start_time between :pStartTime AND :pEndTime) 
                    OR (edt.end_time between :pStartTime AND :pEndTime)
                ) 
            AND m.enseignant_id     = :pEnseignantId
            -- AND edt.matiere_id      <> :pMatiereId
            -- AND m.nom               = :pMatiere     

            GROUP BY s.id
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pDateSchedule',  $params['date'], \PDO::PARAM_STR);
        $statement->bindParam('pEndTime',   $params['endTime'], \PDO::PARAM_STR);
        $statement->bindParam('pStartTime', $params['startTime'], \PDO::PARAM_STR);
        $statement->bindParam('pEnseignantId',  $params['enseignant'], \PDO::PARAM_INT);
        // $statement->bindParam('pMatiereId',  $params['matiereId'], \PDO::PARAM_INT);
        // $statement->bindParam('pMatiere',  $params['matiere'], \PDO::PARAM_STR);
        $statement->executeQuery();
        // dump($statement);die;
        return $result = $statement->fetchAll();
    }

    /**
     * Check if Teacher NOT common core EDT exist at given interval
     * @params mixed
     * @return count item
     * */

    public function getEdtSalle(int $pEdtId) {
        $query = " 
            SELECT DISTINCT s.id, b.nom as batimentNom, s.libelle, s.capacite, s.internet_connexion_on, s.video_projecteur_on, edt.id as edtId, edt.date_schedule, edt.start_time, edt.end_time, m.nom as matiere, edt.tronc_commun FROM emploi_du_temps edt
            INNER JOIN salles s ON s.id = edt.salles_id
            INNER JOIN batiment b ON b.id = s.batiment_id
            INNER JOIN matiere m ON m.id = edt.matiere_id
            WHERE edt.id  = :pEdtId
                
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pEdtId',  $pEdtId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $result = $statement->fetchAll();
    }
}