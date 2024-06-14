<?php

namespace App\Manager;

use Doctrine\ORM\Query\ResultSetMapping;
/**
 * Class FichePresenceEnseignant
 *
 * @package App\Manager
 */
class FichePresenceEnseignantManager extends BaseManager
{
    /**
     * Get sum time for each teacher
     * @param $mentionId
     * return mixed[]
     */
    public function getTeacherMatiereTotalHour(int $mentionId, int $anneeUnivId) {
        $sql = "
            select *, vht-heureEffectue as heureRestante from (select mat.id, mat.nom, CONCAT(en.first_name, ' ', en.last_name) as enseignant, SUM(ROUND(TIME_TO_SEC(TIMEDIFF(fpe.end_time, start_time))/3600, 2)) as heureEffectue, mat.volume_horaire_total as vht FROM  fiche_presence_enseignant fpe 
            join matiere mat on mat.id = fpe.matiere_id
            left join enseignant en on en.id = mat.enseignant_id
            where fpe.mention_id = :mentionId
            and fpe.annee_universitaire_id = :anneeUnivId
            group by fpe.matiere_id) as temp
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get sum time for each teacher
     *
     * @param int $anneeUnivId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTeacherTotalHourByCollegeYear(int $anneeUnivId) {
        $sql = "
            select *, vht-heureEffectue as heureRestante from (select mat.id, mat.nom, CONCAT(en.first_name, ' ', en.last_name) as enseignant, SUM(ROUND(TIME_TO_SEC(TIMEDIFF(fpe.end_time, start_time))/3600, 2)) as heureEffectue, mat.volume_horaire_total as vht FROM  fiche_presence_enseignant fpe 
            join matiere mat on mat.id = fpe.matiere_id
            left join enseignant en on en.id = mat.enseignant_id
            where fpe.annee_universitaire_id = :anneeUnivId
            group by fpe.matiere_id) as temp
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get sum time for each teacher
     * @param $mentionId
     * return mixed[]
     */
    public function getTeacherMatiereDetailHour(int $matiereId) {
        $sql = "
            select fpe.id, fpe.statut status, fpe.theme, mat.id as matId, mat.nom, fpe.date, fpe.start_time, fpe.end_time, CONCAT(en.first_name, ' ', en.last_name), ROUND(TIME_TO_SEC(TIMEDIFF(fpe.end_time, start_time))/3600, 2) as heureEffectue, mat.volume_horaire_total as vht FROM  fiche_presence_enseignant fpe 
            join matiere mat on mat.id = fpe.matiere_id
            left join enseignant en on en.id = mat.enseignant_id
            where fpe.matiere_id = :matiereId
            order by date asc, start_time asc, end_time asc
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('matiereId', $matiereId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * 
     * 
     * */

    public function checkExistingEntry(int $enseignantId, $date, $startTime,$endTime){
        $fpDate         = $date->format("Y-m-d");
        $fpStartTime    = $startTime->format("H:i:s");
        $fpEndTime      = $endTime->format("H:i:s");
        $sql = "
            SELECT * FROM  fiche_presence_enseignant fpe 
            JOIN matiere mat on mat.id = fpe.matiere_id
            WHERE fpe.enseignant_id = :enseignantId
            AND fpe.date = :dateFpresence
            AND 
                (
                    (fpe.start_time > :startTime1 AND fpe.start_time  < :endTime1) OR
                    (fpe.end_time > :startTime1 AND fpe.end_time  < :endTime1)
                )
            
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->bindParam('dateFpresence', $fpDate, \PDO::PARAM_STR);
        
        $statement->bindParam('startTime1', $fpStartTime, \PDO::PARAM_STR);
        $statement->bindParam('endTime1', $fpEndTime, \PDO::PARAM_STR);
        
        $statement->bindParam('startTime2', $fpStartTime, \PDO::PARAM_STR);
        $statement->bindParam('endTime2', $fpEndTime, \PDO::PARAM_STR);
                
        $statement->executeQuery();

        return $statement->fetchAll();;
    }

}