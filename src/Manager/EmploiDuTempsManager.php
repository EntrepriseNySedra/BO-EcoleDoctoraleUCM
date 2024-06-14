<?php

namespace App\Manager;

use App\Entity\AnneeUniversitaire;
use App\Entity\CalendrierPaiement;
use App\Entity\Mention;
use App\Entity\EmploiDuTemps;

/**
 * Class CoursManager
 *
 * @package App\Manager
 */
class EmploiDuTempsManager extends BaseManager
{
    public function getListActive($_mentionId=null, $statut = null, CalendrierPaiement $calPaiement = null, $_niveauId = null, $_week=null, $_date=null) {
        $today = date("Y-m-d");
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $niveauFilter = $_niveauId ? " AND e.niveau_id = :pNiveau " : "";
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_CREATED;
        $qWeekFilter = $_week ? " AND DATE_FORMAT(e.date_schedule, '%U') = :pWeek " : "" ;
        $queryMentionFilter = $_mentionId ? " AND e.mention_id = :pMention " : "" ;
        $qDateFilter = $_date ? " AND DATE_FORMAT(e.date_schedule, '%Y-%m-%d') = :pDate " : "" ;
        $query = "  SELECT e.*, p.nom as parcours, n.libelle as niveau, m.nom as matiere, s.libelle as salle, b.nom as batiment, s.capacite
                    FROM emploi_du_temps e 
                    LEFT JOIN parcours p ON p.id = e.parcours_id
                    INNER JOIN niveau n ON n.id = e.niveau_id
                    INNER JOIN matiere m ON m.id = e.matiere_id
                    INNER JOIN salles s ON s.id = e.salles_id
                    INNER JOIN batiment b ON b.id = s.batiment_id
                    WHERE e.statut IS NOT NULL
                    $queryMentionFilter
                    $calPaiementFilter
                    $niveauFilter
                    $qWeekFilter
                    $qDateFilter
                    ORDER BY e.date_schedule DESC, e.start_time DESC ";//WHERE e.statut = :pStatut

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        //$statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        if($_mentionId)
            $statement->bindParam('pMention', $_mentionId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        if($_niveauId){
            $statement->bindParam('pNiveau', $_niveauId, \PDO::PARAM_INT);
        }
        if($_week){
            $statement->bindParam('pWeek', $_week, \PDO::PARAM_INT);
        }
        if($_date){
            $statement->bindParam('pDate', $_date, \PDO::PARAM_STR);
        }

        $statement->executeQuery();
        
        return $statement->fetchAll();
    }

    public function getEdtByParams(array $tParams=[]) {
        $query = "  SELECT e.* FROM emploi_du_temps e  "
                    . "WHERE e.date_schedule = '" . $tParams['dateSchedule'] . "' "
                    . "AND e.start_time >= '".$tParams['startTime']."' "
                    . "AND e.end_time <= '".$tParams['endTime']."' "
                    . "ORDER BY e.id ASC ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get EDT for current day
     * return edt[] 
     * */    
    public function getByCurrentDay($_niveauId, $_mentionId, $_parcoursId, $_anneeUnivId, $_date=null) {
        $today = $_date ? $_date : date('Y-m-d');
        $queryParcoursFilter = $_parcoursId ? "AND e.parcours_id = :parcoursId" : "";
        $query = "  SELECT e.*, m.nom, s.libelle, b.nom as batiment, parc.nom as parcoursNom, niv.libelle as niveauLibelle FROM emploi_du_temps e 
                    INNER JOIN matiere m ON m.id = e.matiere_id
                    INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
                    INNER JOIN mention men ON men.id = ue.mention_id
                    INNER JOIN niveau niv ON niv.id = ue.niveau_id
                    LEFT JOIN parcours parc ON parc.id = ue.parcours_id
                    INNER JOIN salles s ON s.id = e.salles_id
                    INNER JOIN batiment b ON b.id = s.batiment_id
                    WHERE e.date_schedule = :current_date

                    AND e.mention_id = :mentionId
                    AND e.niveau_id = :niveauId
                    AND e.annee_universitaire_id = :anneeUnivId
                    $queryParcoursFilter

                    ORDER BY e.date_schedule ASC ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('current_date', $today, \PDO::PARAM_STR);
        $statement->bindParam('mentionId', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $_niveauId, \PDO::PARAM_INT);
        $statement->bindParam('anneeUnivId', $_anneeUnivId, \PDO::PARAM_INT);
        if($_parcoursId)
            $statement->bindParam('parcoursId', $_parcoursId, \PDO::PARAM_INT);
        $statement->executeQuery();
        return $statement->fetchAll();
    }

    /**
     * Get EDT for current week
     * return edt[] 
     * */    
    public function getByCurrentWeek($_niveauId, $_mentionId, $_parcoursId, $_anneeUnivId, $_date=null) {
        $curWeek = $_date ? (new \DateTime($_date))->format('W') : date('W');
        $queryParcoursFilter = $_parcoursId ? "AND e.parcours_id = :parcoursId" : "";
        $query = "  SELECT e.*, m.nom, s.libelle, b.nom as batiment, parc.nom as parcoursNom, niv.libelle as niveauLibelle FROM emploi_du_temps e 
                    INNER JOIN matiere m ON m.id = e.matiere_id
                    INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
                    INNER JOIN mention men ON men.id = ue.mention_id
                    INNER JOIN niveau niv ON niv.id = ue.niveau_id
                    LEFT JOIN parcours parc ON parc.id = ue.parcours_id 
                    INNER JOIN salles s ON s.id = e.salles_id
                    INNER JOIN batiment b ON b.id = s.batiment_id

                    WHERE date_format(e.date_schedule, '%u') = :current_week
                    AND e.mention_id = :mentionId
                    AND e.niveau_id = :niveauId
                    AND e.annee_universitaire_id = :anneeUnivId
                    $queryParcoursFilter

                    ORDER BY e.date_schedule ASC ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);

        $statement->bindParam('current_week', $curWeek, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $_niveauId, \PDO::PARAM_INT);
        $statement->bindParam('anneeUnivId', $_anneeUnivId, \PDO::PARAM_INT);
        if($_parcoursId)
            $statement->bindParam('parcoursId', $_parcoursId, \PDO::PARAM_INT);
        $statement->executeQuery();

        // dump($query);die;

        return $statement->fetchAll();
    }

    /**
     * */
    public function getByMatiereOnCurrentDate($tMatiere, $anneeUniversitaire) {
        $today = date("Y-m-d");
        return $this->getRepository()->createQueryBuilder('edt')
            ->where('edt.anneeUniversitaire = :anneeUniv')
            ->andWhere('edt.matiere IN (:tMatiere)')
            ->andWhere("DATE_FORMAT(edt.dateSchedule, '%Y-%m-%d') = :today")
            ->setParameter('anneeUniv', $anneeUniversitaire)
            ->setParameter('tMatiere', $tMatiere)
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * */
    public function getByMatiereOnCurrentWeek($tMatiere, $anneeUniversitaire) {
        $currentWeek = date("W");
        return $this->getRepository()->createQueryBuilder('edt')
            ->where('edt.anneeUniversitaire = :anneeUniv')
            ->andWhere('edt.matiere IN (:tMatiere)')
            ->andWhere("DATE_FORMAT(edt.dateSchedule, '%u') = :week")
            ->setParameter('anneeUniv', $anneeUniversitaire)
            ->setParameter('tMatiere', $tMatiere)
            ->setParameter('week', $currentWeek)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param \App\Entity\AnneeUniversitaire $anneeUniversitaire
     * @param \App\Entity\Mention            $mention
     *
     * @return mixed
     */
    public function getByMentionAndOrCollegeYear(AnneeUniversitaire $anneeUniversitaire, Mention $mention = null)
    {
        $qb = $this->getRepository()->createQueryBuilder('edt')
                   ->where('edt.anneeUniversitaire = :anneeUniversitaire')
                   ->setParameter('anneeUniversitaire', $anneeUniversitaire)
        ;
        if ($mention) {
            $qb->andWhere('edt.mention = :mention')
               ->setParameter('mention', $mention)
            ;
        }

        $qb->orderBy('edt.dateSchedule', 'DESC');

        return $qb->getQuery()
                  ->getResult()
            ;
    }

    /**
     * Get current period vacation groub by common core
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getCurrentVacationGroupTronCommun(int $anneeUnivId, $calPaiement=null, $statut=null) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_CREATED;
        $query = "SELECT X.ensId, CONCAT(X.first_name, ' ', X.last_name) as enseignant, count(X.ensId) as nbrMatiere, SUM(totalHeureMatiere) as totalH, ROUND((SUM(totalHeureMatiere)*taux), 2) as montantHT FROM (
                SELECT edt.id as edtId, e.id as ensId, e.first_name, e.last_name, m.nom, SUM(ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2)) as totalHeureMatiere, m.taux_horaire as taux
                FROM emploi_du_temps edt 
                INNER JOIN matiere m ON m.id = edt.matiere_id
                INNER JOIN enseignant e ON e.id = m.enseignant_id
                WHERE edt.statut = :pStatut
                AND edt.mention_id = :pMention
                AND edt.annee_universitaire_id = :pAnneeUniv
                $calPaiementFilter
                GROUP BY m.id ) X 
        GROUP BY ensId";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
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
     * Get current period vacation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getCurrentVacation(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $queryMentionFilter = $mentionId ? " AND edt.mention_id = :pMention " : "";
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_CREATED;
        $query = "SELECT X.ensId, CONCAT(X.last_name, ' ', X.first_name) as enseignant, count(X.ensId) as nbrMatiere, SUM(totalHeureMatiere) as totalH
        , ROUND(SUM(totalHT), 2) as montantHT, GROUP_CONCAT(X.statut_1 SEPARATOR ',') as statut_2 FROM (                

                SELECT Y.edtId, Y.ensId, Y.first_name, Y.last_name, Y.nom, SUM(ROUND(TIME_TO_SEC(TIMEDIFF(Y.end_time, Y.start_time))/3600, 2)) as totalHeureMatiere, 
                 Y.taux, SUM(montant) as totalHT, GROUP_CONCAT(Y.statut SEPARATOR ',') as statut_1 FROM (

                    SELECT edt.id as edtId, e.id as ensId, e.first_name, e.last_name, m.id as matId, m.nom, m.taux_horaire as taux, edt.end_time, edt.start_time, ((ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2)) * m.taux_horaire) as montant, GROUP_CONCAT(edt.statut SEPARATOR ',') as statut
                    FROM emploi_du_temps edt 
                    INNER JOIN matiere m ON m.id = edt.matiere_id
                    INNER JOIN enseignant e ON e.id = m.enseignant_id
                    -- WHERE edt.statut = :pStatut
                    WHERE edt.statut IS NOT NULL
                    $queryMentionFilter
                    AND edt.annee_universitaire_id = :pAnneeUniv
                    $calPaiementFilter
                    GROUP BY e.id, edt.date_schedule, edt.start_time, edt.end_time, edt.tronc_commun

                ) Y

                GROUP BY matId ) X 
        GROUP BY ensId
        ORDER BY enseignant ASC ";
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
     * Get enseignant vacation
     * @param $enseignantId
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getEnseignantVacation($enseignantId, int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null) {
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_CREATED;
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $queryMentionFilter = $mentionId ? " AND edt.mention_id = :pMention " : "";
        $query = "
                SELECT edt.id as edtId, e.id as ensId, e.first_name, e.last_name, m.nom as matiere, m.code as mCode, edt.date_schedule, edt.start_time, edt.end_time, ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2) as heure, edt.tronc_commun as troncCommun, s.libelle as salle, men.nom as mention, parc.nom as parcours, niv.libelle as niveau, ROUND(((ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2)) * m.taux_horaire), 2) as montantHT, 
                GROUP_CONCAT( CONCAT(niv.libelle, ' ', men.nom, ' ', COALESCE(parc.nom,'')) SEPARATOR '<br>') as classList, edt.statut, men.nom as authorMention, m.taux_horaire
                FROM emploi_du_temps edt 
                INNER JOIN mention men ON men.id = edt.mention_id
                INNER JOIN niveau niv ON niv.id = edt.niveau_id
                LEFT JOIN parcours parc ON parc.id = edt.parcours_id
                INNER JOIN matiere m ON m.id = edt.matiere_id
                INNER JOIN enseignant e ON e.id = m.enseignant_id
                INNER JOIN salles s ON s.id = edt.salles_id
                -- WHERE edt.statut = :pStatut
                WHERE edt.statut IS NOT NULL
                AND m.enseignant_id = :pEnseignant
                $queryMentionFilter
                AND edt.annee_universitaire_id = :pAnneeUniv
                $calPaiementFilter
                GROUP BY e.id, edt.salles_id, edt.date_schedule, edt.start_time, edt.end_time, edt.tronc_commun, edt.id
                ORDER By niveau, mention, parcours, edt.date_schedule, edt.start_time, m.nom"; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        // $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        $statement->bindParam('pEnseignant', $enseignantId, \PDO::PARAM_INT);
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
     * Get enseignant vacation per matiere
     * @param $enseignantId
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getEnseignantVacationPerMat($vacations) {
        $result = [];
        foreach ($vacations as $key => $value) {
            $matCode = $value['mCode'];
            if(!array_key_exists($matCode, $result)) {
                $result[$matCode] = []; 
                $result[$matCode]['totalTTC'] = 0;
                $result[$matCode]['totalQte'] = 0;    
                $result[$matCode]['totalImpot'] = 0;
                $result[$matCode]['totalHT'] = 0;
                $result[$matCode]['details'] = [];
                $result[$matCode]['size'] = 1;
            } else {
                $result[$matCode]['size']++;    
            }

            $result[$matCode]['totalTTC'] += $value['montantHT'] / 0.98;
            $result[$matCode]['totalQte'] += $value['heure'];
            $result[$matCode]['totalImpot'] += ($value['montantHT']/0.98) * 0.02;
            $result[$matCode]['totalHT'] += $value['montantHT'];
            $result[$matCode]['details'][] = $value;
        }

        return $result;        
    }

    /**
     * Get exportable vacation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getExportableVacation(int $anneeUnivId, $calPaiement=null, $statut=null, $mentionId = null) {
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_COMPTA_VALIDATED;
        $calPaiementFilter = $calPaiement ? 
            "
                AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $queryMentionFilter = $mentionId ? " AND edt.mention_id = :pMention " : "";
        $query = "
                SELECT edt.id as edtId, e.id as ensId, e.first_name, e.last_name, m.nom as matiere, m.code as mCode, edt.date_schedule, edt.start_time, edt.end_time, ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2) as heure, edt.tronc_commun as troncCommun, s.libelle as salle, men.nom as mention, parc.nom as parcours, niv.libelle as niveau, ROUND(m.taux_horaire, 2) as taux_horaire, e.immatricule, edt.statut
                FROM emploi_du_temps edt 
                INNER JOIN mention men ON men.id = edt.mention_id
                INNER JOIN niveau niv ON niv.id = edt.niveau_id
                LEFT JOIN parcours parc ON parc.id = edt.parcours_id
                INNER JOIN matiere m ON m.id = edt.matiere_id
                INNER JOIN enseignant e ON e.id = m.enseignant_id
                INNER JOIN salles s ON s.id = edt.salles_id
                -- WHERE edt.statut IS NOT NULL
                WHERE edt.statut > :pStatut
                AND edt.annee_universitaire_id = :pAnneeUniv
                $calPaiementFilter
                $queryMentionFilter
                GROUP BY e.id, edt.date_schedule, edt.start_time, edt.end_time, edt.tronc_commun

                ORDER By niveau, mention, parcours, edt.date_schedule, edt.start_time, m.nom"; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
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
     * Get exportable vacation comptable
     * @return mixed data
     */
    public function getExportableVacationCompta(int $anneeUnivId, $calPaiement=null, $statut=null, $mentionId) {
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_COMPTA_VALIDATED;
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $queryMentionFilter = $mentionId ? " AND edt.mention_id = :pMention " : "";
        $query = "
                    SELECT *, SUM(montant) as montantHT FROM (
                        SELECT e.id as ensId, e.first_name, e.last_name, e.immatricule, edt.tronc_commun as troncCommun, men.nom as mention, parc.nom as parcours, niv.id as nivId, niv.libelle as niveau, (ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2) * m.taux_horaire) as montant, men.num_compte_generale, e.tiers_count
                        FROM emploi_du_temps edt 
                        INNER JOIN mention men ON men.id = edt.mention_id
                        INNER JOIN niveau niv ON niv.id = edt.niveau_id
                        LEFT JOIN parcours parc ON parc.id = edt.parcours_id
                        INNER JOIN matiere m ON m.id = edt.matiere_id
                        INNER JOIN enseignant e ON e.id = m.enseignant_id
                        INNER JOIN salles s ON s.id = edt.salles_id
                        -- WHERE edt.statut IS NOT NULL
                        WHERE edt.statut > :pStatut
                        AND edt.annee_universitaire_id = :pAnneeUniv
                        $calPaiementFilter
                        $queryMentionFilter
                        GROUP BY e.id, edt.date_schedule, edt.start_time, edt.end_time, edt.tronc_commun
                        ORDER By e.first_name, e.last_name
                    ) X
                    GROUP BY X.ensId, X.mention, X.nivId, X.parcours
                "; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
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
     * Get exportable vacation per enseignant
     * @return mixed data
     */
    public function getExportableVacationPerEns(int $anneeUnivId, $calPaiement=null, $statut=null, $mentionId=null) {
        $pStatut = $statut ? $statut : EmploiDuTemps::STATUS_COMPTA_VALIDATED;
        $calPaiementFilter = $calPaiement ? " AND date_schedule BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $queryMentionFilter = $mentionId ? " AND edt.mention_id = :pMention " : "";
        $query = "
                    SELECT *, SUM(montant) as montantHT FROM (
                        SELECT e.id as ensId, e.first_name, e.last_name, e.immatricule, e.bank_num, edt.tronc_commun as troncCommun, men.nom as mention, parc.nom as parcours, niv.id as nivId, niv.libelle as niveau, (ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2) * m.taux_horaire) as montant, men.num_compte_generale, e.tiers_count
                        FROM emploi_du_temps edt 
                        INNER JOIN mention men ON men.id = edt.mention_id
                        INNER JOIN niveau niv ON niv.id = edt.niveau_id
                        LEFT JOIN parcours parc ON parc.id = edt.parcours_id
                        INNER JOIN matiere m ON m.id = edt.matiere_id
                        INNER JOIN enseignant e ON e.id = m.enseignant_id
                        INNER JOIN salles s ON s.id = edt.salles_id
                        -- WHERE edt.statut IS NOT NULL
                        WHERE edt.statut > :pStatut
                        AND edt.annee_universitaire_id = :pAnneeUniv
                        $calPaiementFilter
                        $queryMentionFilter
                        -- AND edt.mention_id = 4
                        GROUP BY e.id, edt.date_schedule, edt.start_time, edt.end_time, edt.tronc_commun
                        ORDER By e.first_name, e.last_name
                    ) X
                    GROUP BY X.ensId
                "; 
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
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
     * Get enseignant volume horaire
     * @return mixed data
     */
    public function getEnseignantVolumeHoraire(int $anneeUnivId, int $enseignantId) {
        $query = "
            Select mat.id, mat.nom as matiere, volume_horaire_total, SUM(ROUND(TIME_TO_SEC(TIMEDIFF(edt.end_time, edt.start_time))/3600, 2)) as totalHeureMatiere, men.nom as mention, niv.libelle as niveau, parc.nom as parcours FROM matiere mat 
            INNER JOIN emploi_du_temps edt on edt.matiere_id = mat.id
            INNER JOIN mention men ON men.id = edt.mention_id
            INNER JOIN niveau niv ON niv.id = edt.niveau_id
            LEFT JOIN parcours parc ON parc.id = edt.parcours_id
            WHERE enseignant_id = :pEnseignant
            AND edt.annee_universitaire_id = :pAnneeUniv
            GROUP BY mat.id
            ORDER BY men.id ASC, niv.id ASC
        "; 

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pEnseignant', $enseignantId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}