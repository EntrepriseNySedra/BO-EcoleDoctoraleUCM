<?php

namespace App\Manager;

use App\Repository\CalendrierExamenRepository;
use App\Services\WorkflowStatutService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CalendrierExamenManager
 *
 * @package App\Manager
 */
class CalendrierExamenManager extends BaseManager
{
    /**
     * Get all
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getAll(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null, $niveau=null, $_week, $_date) {
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $queryMentionFilter = $mentionId ? " AND clx.mention_id = :pMention " : "";
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_CREATED;
        $queryNiveauFilter = $niveau ? " AND clx.niveau_id = :pNiveau " : "";
        $qWeekFilter = $_week ? " AND DATE_FORMAT(clx.date_schedule, '%U') = :pWeek " : "" ;
        $qDateFilter = $_date ? " AND DATE_FORMAT(clx.date_schedule, '%Y-%m-%d') = :pDate " : "" ;
        $query = "                            
            SELECT clx.id, clx.libelle, clx.date_schedule, d.nom as departement, m.nom as mention, p.nom as parcours, n.libelle as niveau, clx.start_time, clx.end_time, mt.nom as matiere, ue.libelle as ue, clx.statut
            FROM calendrier_examen clx 
            LEFT JOIN calendrier_examen_surveillance ces ON ces.calendrier_examen_id = clx.id
            LEFT JOIN user u ON u.id = ces.surveillant_id
            INNER JOIN departement d ON d.id = clx.departement_id
            INNER JOIN mention m ON m.id = clx.mention_id
            LEFT JOIN parcours p ON p.id = clx.parcours_id
            INNER JOIN niveau n ON n.id = clx.niveau_id
            INNER JOIN unite_enseignements ue ON ue.id = clx.unite_enseignements_id
            INNER JOIN matiere mt ON mt.id = clx.matiere_id
            -- WHERE edt.statut = :pStatut
            WHERE clx.statut IS NOT NULL
            $queryMentionFilter
            $queryNiveauFilter
            $qWeekFilter
            $qDateFilter
            AND clx.annee_universitaire_id = :pAnneeUniv
            $calPaiementFilter
            GROUP BY clx.id, clx.date_schedule, clx.start_time, clx.end_time, clx.tronc_commun
            ORDER BY clx.date_schedule
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        // dump($statement);die;
        // $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        if($niveau)
            $statement->bindParam('pNiveau', $niveau, \PDO::PARAM_INT);
        if($_week){
            $statement->bindParam('pWeek', $_week, \PDO::PARAM_INT);
        }
        if($_date){
            $statement->bindParam('pDate', $_date, \PDO::PARAM_STR);
        }
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get current period surveillance
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getCurrentVacation(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null) {

      
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $queryMentionFilter = $mentionId ? " AND clx.mention_id = :pMention " : "";
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_CREATED;
        $query = "              

                SELECT *, SUM(quantite) as totalHeure, GROUP_CONCAT(Y.statut SEPARATOR ',') as status_1, COUNT(Y.clxId) as nbrExamen FROM (

                    SELECT clx.id as clxId, clx.end_time, clx.start_time, (ROUND(TIME_TO_SEC(TIMEDIFF(clx.end_time, clx.start_time))/3600, 2)) as quantite, clx.statut, CONCAT(u.first_name, ' ', u.last_name) as surveillantName, u.id as surveillantId, clx.tronc_commun
                    FROM calendrier_examen clx 
                    INNER JOIN calendrier_examen_surveillance ces ON ces.calendrier_examen_id = clx.id
                    INNER JOIN user u ON u.id = ces.surveillant_id
                    -- WHERE edt.statut = :pStatut
                    WHERE clx.statut IS NOT NULL
                    $queryMentionFilter
                    AND clx.annee_universitaire_id = :pAnneeUniv
                    $calPaiementFilter
                    GROUP BY u.id, clx.id, clx.date_schedule, clx.start_time, clx.end_time, clx.tronc_commun
                ) Y

                GROUP BY Y.surveillantId
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        // $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get Surveillant vacation
     * @param $surveillantId
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getSurveillantVacation($surveillantId, int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null) {
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_CREATED;
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $queryMentionFilter = $mentionId ? " AND clx.mention_id = :pMention " : "";
        $query = "
                SELECT clx.id as clxId, u.id as surveillantId, CONCAT(u.first_name, ' ', u.last_name) as surveillantName, clx.date_schedule, clx.start_time, clx.end_time, ROUND(TIME_TO_SEC(TIMEDIFF(clx.end_time, clx.start_time))/3600, 2) as heure, clx.tronc_commun as troncCommun, men.nom as mention, parc.nom as parcours, niv.libelle as niveau, m.code as mCode, m.nom as matiere, GROUP_CONCAT( CONCAT(niv.libelle, ' ', men.nom, ' ', COALESCE(parc.nom,'')) SEPARATOR '<br>') as classList, clx.statut, men.nom as authorMention
                FROM calendrier_examen clx 
                INNER JOIN calendrier_examen_surveillance ces ON ces.calendrier_examen_id = clx.id
                INNER JOIN user u ON u.id = ces.surveillant_id
                INNER JOIN matiere m ON m.id = clx.matiere_id
                INNER JOIN mention men ON men.id = clx.mention_id
                INNER JOIN niveau niv ON niv.id = clx.niveau_id
                LEFT JOIN parcours parc ON parc.id = clx.parcours_id
                
                -- WHERE edt.statut = :pStatut
                WHERE clx.statut IS NOT NULL
                AND ces.surveillant_id = :pSurveillant
                $queryMentionFilter
                AND clx.annee_universitaire_id = :pAnneeUniv
                $calPaiementFilter
                GROUP BY u.id, clx.id, clx.date_schedule, clx.start_time, clx.end_time, clx.tronc_commun
                ORDER By niveau, mention, parcours, clx.date_schedule, clx.start_time"; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        // $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        $statement->bindParam('pSurveillant', $surveillantId, \PDO::PARAM_INT);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);

        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get exportable vacation comptable
     * @return mixed data
     */
    public function getExportableVacationCompta(int $anneeUnivId, $calPaiement=null, $statut=null, $mentionId=null) {
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_RECTEUR_VALIDATED;
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $queryMentionFilter = $mentionId ? " AND cle.mention_id = :pMention " : "";
        $query = "
                    SELECT *, SUM(quantite) as totalQte FROM (
                        SELECT u.id as survId, epl.first_name, epl.last_name, epl.matricule, men.nom as mention, parc.nom as parcours, niv.id as nivId, niv.libelle as niveau, (ROUND(TIME_TO_SEC(TIMEDIFF(cle.end_time, cle.start_time))/3600, 2)) as quantite, men.num_compte_generale, epl.tiers_num
                        FROM calendrier_examen cle 
                        INNER JOIN calendrier_examen_surveillance ces ON ces.calendrier_examen_id = cle.id
                        INNER JOIN user u ON u.id = ces.surveillant_id
                        INNER JOIN mention men ON men.id = cle.mention_id
                        INNER JOIN niveau niv ON niv.id = cle.niveau_id
                        LEFT JOIN parcours parc ON parc.id = cle.parcours_id
                        INNER JOIN matiere m ON m.id = cle.matiere_id
                        INNER JOIN employe epl ON epl.user_id = u.id
                        WHERE cle.statut IS NOT NULL
                        AND cle.annee_universitaire_id = :pAnneeUniv
                        $calPaiementFilter
                        $queryMentionFilter
                        GROUP BY ces.surveillant_id, cle.id, cle.date_schedule, cle.start_time, cle.end_time, cle.tronc_commun
                        ORDER By epl.first_name, epl.last_name
                    ) X
                    GROUP BY X.survId, X.mention, X.nivId, X.parcours
                "; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        if($mentionId){
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        }

        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get period surveillance per surveillant 
     * @return mixed data
     */
    public function getEtatPerSurveillant(int $anneeUnivId, $calPaiement=null, $statut=null, $mentionId=null) {
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";        
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_RECTEUR_VALIDATED;
        $queryMentionFilter = $mentionId ? " AND clx.mention_id = :pMention " : "";
        $query = "              
                SELECT *, SUM(quantite) as totalHeure, GROUP_CONCAT(Y.statut SEPARATOR ',') as status_1, COUNT(Y.clxId) as nbrExamen, GROUP_CONCAT(Y.matiere SEPARATOR ',') as matieres FROM (

                    SELECT clx.id as clxId, clx.end_time, clx.start_time, (ROUND(TIME_TO_SEC(TIMEDIFF(clx.end_time, clx.start_time))/3600, 2)) as quantite, clx.statut, CONCAT(epl.first_name, ' ', epl.last_name) as surveillantName, u.id as surveillantId, clx.tronc_commun, epl.matricule, men.nom as mention, niv.libelle as niveau, parc.nom as parcours, mat.nom as matiere
                    FROM calendrier_examen clx 
                    INNER JOIN calendrier_examen_surveillance ces ON ces.calendrier_examen_id = clx.id
                    INNER JOIN user u ON u.id = ces.surveillant_id
                    INNER JOIN mention men ON men.id = clx.mention_id
                    INNER JOIN niveau niv ON niv.id = clx.niveau_id
                    LEFT JOIN parcours parc ON parc.id = clx.parcours_id
                    INNER JOIN matiere mat ON mat.id = clx.matiere_id
                    INNER JOIN employe epl ON epl.user_id = u.id
                    WHERE clx.statut IS NOT NULL
                    AND clx.annee_universitaire_id = :pAnneeUniv
                    $calPaiementFilter
                    $queryMentionFilter
                    GROUP BY ces.surveillant_id, clx.id, clx.date_schedule, clx.start_time, clx.end_time, clx.tronc_commun
                ) Y
                GROUP BY Y.surveillantId
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        if($mentionId){
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        }

        $statement->executeQuery();

        return $statement->fetchAll();
    }

}