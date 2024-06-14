<?php

namespace App\Manager;
use App\Entity\Prestation;
use App\Entity\Profil;
use App\Services\WorkflowStatutService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
/**
 * Class TypePrestationManager
 *
 * @package App\Manager
 */
class PrestationManager extends BaseManager
{
    /**
     * Get list of prestation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getListGroupById(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=null) {
        //dump($calPaiement);die;
        $calPaiementFilter = $calPaiement ? 
            "
                AND date BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $pStatut = $statut ? $statut : Prestation::STATUS_CREATED;
        $mentionFilter = $mentionId ? "AND pst.mention_id = :pMention" : "AND tpstm.mention_id IS NOT NULL";
        $query = "
            SELECT X.*, GROUP_CONCAT(X.userName SEPARATOR ',') as beneficiaire FROM
            (
                SELECT pst.id as prestationId, pst.quantite, pst.date, tpst.designation, pst.statut, CONCAT(u.first_name, ' ', u.last_name) as userName
                FROM prestation pst 
                INNER JOIN user u ON u.id = pst.user_id
                INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id
                WHERE pst.annee_universitaire_id = :pAnneeUniv
                AND pst.statut = :pStatut
                $mentionFilter
                $calPaiementFilter
            ) X
            GROUP BY prestationId
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
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
     * Get current period prestation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getListGroupByAthor(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=array()) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        // $pStatut = $statut ? $statut : Prestation::STATUS_CREATED;
        $mentionFilter = $mentionId ? "AND pst.mention_id = :pMention" : "";
        $mentionJoin = $mentionId ? "INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id": "";
        $query = "   
            SELECT *, SUM(quantite) as totalQte, SUM(ssTotal) as total FROM 
            (         
                SELECT pst.quantite, (pst.quantite*tpst.taux) as ssTotal, pst.statut, CONCAT(u.first_name, u.last_name) as userName, auteur_id
                FROM prestation pst 
                INNER JOIN user u ON u.id = pst.auteur_id
                INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                $mentionJoin
                WHERE pst.annee_universitaire_id = :pAnneeUniv
                -- AND pst.statut = :pStatut
                $mentionFilter
                $calPaiementFilter
            ) X 

            GROUP BY auteur_id
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        // $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
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
     * Get current period prestation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getListResumeByUser(int $authorId, int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=array()) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $pStatut = $statut ? join(',', $statut) : Prestation::STATUS_CREATED;
        $mentionFilter = $mentionId ? "AND tpstm.mention_id = :pMention" : "";
        $mentionJoin = $mentionId ? "INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id": "";
        $query = "   
                    
            SELECT SUM(pst.quantite) as totalQte, SUM(pst.quantite*tpst.taux) as ssTotal, pst.statut, CONCAT(u.first_name,' ', u.last_name) as userName,user_id, COUNT(statut) as nbrStatut
            FROM prestation pst 
            INNER JOIN user u ON u.id = pst.user_id
            INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
            $mentionJoin
            WHERE pst.annee_universitaire_id = :pAnneeUniv
            AND pst.auteur_id = :pAuhtor
            $mentionFilter
            $calPaiementFilter

           GROUP BY user_id, statut
           
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pAuhtor', $authorId, \PDO::PARAM_INT);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        $statement->executeQuery();
        $sqlResult = $statement->fetchAll();
        $results = [];
        foreach($sqlResult  as $item) {
            if(!array_key_exists($item['user_id'], $results)){
                $resItem =  $results[$item['user_id']] = [];
                $resItem['userName'] = $item['userName'];
                $resItem['totalQte'] = 0;
                $resItem['ssTotal'] = 0;
                $resItem['nbrStatut'] = 0;
                $resItem['statutItem'] = [];
            }
            $resItem['totalQte'] += $item['totalQte'];
            $resItem['ssTotal'] += $item['ssTotal'];
            $resItem['nbrStatut'] += $item['nbrStatut'];
            $resItem['statutItem'][$item['statut']] = $item['nbrStatut'];
            $results[$item['user_id']] = $resItem;
        }
        return $results;
    }

    /**
     * Get current period prestation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getUserValidatablePrestation(int $userId, int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=array()) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $pStatut = $statut && count($statut) > 0 ? "'".join(',', $statut)."'" : Prestation::STATUS_CREATED;
        $mentionSqlFilter = $mentionId ? "AND tpstm.mention_id = :pMention" : "";
        $prestStatutSqlFilter = $statut && count($statut) > 0 ? "pst.statut IN ($pStatut)" : "";
        $mentionJoin = $mentionId ? "INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id": "";
        $query = "
                SELECT *, (totalQte*taux) as total, (totalQte*taux*0.02) as impot, ((totalQte*taux)-(totalQte*taux*0.02)) as net FROM 
                ( 
                     SELECT *, SUM(Y.quantite) as totalQte FROM
                        (
                            SELECT pst.id as prestationId, pst.quantite, pst.date, pst.statut, CONCAT(u.first_name, u.last_name) as userName, u.id as userId, tpst.designation, tpst.taux, tpst.id as tpstId, tpst.unite, men.nom as authorMention
                            FROM prestation pst 
                            INNER JOIN user u ON u.id = pst.user_id
                            INNER JOIN user a ON a.id = pst.auteur_id
                            INNER JOIN mention men ON men.id = a.mention_id
                            INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                            $mentionJoin
                            WHERE pst.annee_universitaire_id = :pAnneeUniv
                            AND pst.user_id = :pUser
                            $prestStatutSqlFilter
                            $mentionSqlFilter
                            $calPaiementFilter
                        ) Y
                    GROUP BY prestationId, userId, tpstId
                    ORDER BY date
                ) X
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pUser', $userId, \PDO::PARAM_INT);
        // if($statut && count($statut) > 0)
        //     $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_STR);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
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
     * Get current period prestation
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getValidatablePrestation(int $anneeUnivId, $mentionId=null, $calPaiement=null, $statut=array(), $authorId=null) {
        $calPaiementFilter = $calPaiement ? 
            "
                AND date BETWEEN :pCalDateDebut AND :pCalDateFin
            ":"";
        $pStatut = $statut && count($statut) > 0 ? "'".join(',', $statut)."'" : Prestation::STATUS_CREATED;
        $mentionSqlFilter = $mentionId ? "AND tpstm.mention_id = :pMention" : "";
        $prestStatutSqlFilter = $statut && count($statut) > 0 ? "pst.statut IN ($pStatut)" : "";
        $mentionJoin = $mentionId ? "INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id": "";
        $authorFilter = $authorId ? "AND pst.auteur_id = :pAuthor" : "";
        $query = "
                SELECT *, (totalQte*taux) as total, (totalQte*taux*0.02) as impot, ((totalQte*taux)-(totalQte*taux*0.02)) as net FROM 
                ( 
                     SELECT *, SUM(Y.quantite) as totalQte FROM
                        (
                            SELECT pst.id as prestationId, pst.quantite, pst.date, pst.statut, CONCAT(u.first_name, u.last_name) as userName, u.id as userId, tpst.designation, tpst.taux, tpst.id as tpstId, CONCAT(a.first_name, ' ',  a.last_name) as authorName, tpst.unite
                            FROM prestation pst 
                            INNER JOIN user u ON u.id = pst.user_id
                            INNER JOIN user a ON a.id = pst.auteur_id
                            INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                            $mentionJoin
                            WHERE pst.annee_universitaire_id = :pAnneeUniv
                            $prestStatutSqlFilter
                            $mentionSqlFilter
                            $authorFilter
                            $calPaiementFilter
                        ) Y
                    GROUP BY prestationId, userId, tpstId
                    ORDER BY date
                ) X
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        // if($statut && count($statut) > 0)
        //     $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_STR);
        if($mentionId)
            $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        if($authorId)
            $statement->bindParam('pAuthor', $authorId, \PDO::PARAM_INT);
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
     * Get exportable compta
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getExportableCompta(int $anneeUnivId, $calPaiement=null, $statut=null) {
        //dump($calPaiement);die;
        $enseignantProfil = Profil::ENSEIGNANT;
        $calPaiementFilter = $calPaiement ? " AND date BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_RECTEUR_VALIDATED;
        $query = "
                SELECT SUM(X.quantite) as totalQte, X.* FROM (
                    SELECT pst.id as prestationId, pst.quantite, pst.date, tpst.designation, pst.statut, CONCAT(u.first_name, u.last_name) as userName, men.num_compte_generale, epl.tiers_num, tpst.id as typePrestaId, u.id as userId, tpstm.id as tpstmId, men.id as mentionId, tpst.taux, IF(p.name = :pProfil , ens.tiers_count, epl.tiers_num) as compte_tiers
                    FROM prestation pst 
                    INNER JOIN user u ON u.id = pst.user_id
                    INNER JOIN profil p ON p.id = u.profil_id
                    LEFT JOIN employe epl ON epl.user_id = u.id
                    LEFT JOIN enseignant ens ON ens.user_id = u.id
                    INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                    INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id
                    INNER JOIN mention men ON men.id = tpstm.mention_id
                    WHERE pst.annee_universitaire_id = :pAnneeUniv
                    AND pst.statut = :pStatut
                    $calPaiementFilter
                    GROUP BY pst.id) X
                GROUP BY userId, typePrestaId          
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pProfil', $enseignantProfil, \PDO::PARAM_STR);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
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
     * Get exportable per ens
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getExportablePerUser(int $anneeUnivId, $calPaiement=null, $statut=null) {
        //dump($calPaiement);die;
        $enseignantProfil = Profil::ENSEIGNANT;
        $calPaiementFilter = $calPaiement ? " AND date BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $pStatut = $statut ? $statut : WorkflowStatutService::STATUS_RECTEUR_VALIDATED;
        $query = "
                SELECT SUM(X.quantite) as totalQte, X.* FROM (
                    SELECT pst.id as prestationId, pst.quantite, pst.date, tpst.designation, pst.statut, CONCAT(u.first_name, u.last_name) as userName, men.num_compte_generale, epl.tiers_num, tpst.id as typePrestaId, u.id as userId, tpstm.id as tpstmId, men.id as mentionId, tpst.taux, IF(p.name = :pProfil , ens.tiers_count, epl.tiers_num) as compte_tiers, IF(p.name = :pProfil , ens.bank_num, epl.bank_num) as bankNum
                    FROM prestation pst 
                    INNER JOIN user u ON u.id = pst.user_id
                    INNER JOIN profil p ON p.id = u.profil_id
                    LEFT JOIN employe epl ON epl.user_id = u.id
                    LEFT JOIN enseignant ens ON ens.user_id = u.id
                    INNER JOIN type_prestation tpst ON tpst.id = pst.type_prestation_id
                    INNER JOIN type_prestation_mention tpstm ON tpstm.type_prestation_id = tpst.id
                    INNER JOIN mention men ON men.id = tpstm.mention_id
                    WHERE pst.annee_universitaire_id = :pAnneeUniv
                    AND pst.statut = :pStatut
                    $calPaiementFilter
                    GROUP BY pst.id) X
                GROUP BY userId          
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->bindParam('pProfil', $enseignantProfil, \PDO::PARAM_STR);
        $statement->bindParam('pAnneeUniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pStatut', $pStatut, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        $statement->executeQuery();

        return $statement->fetchAll();
    }

}