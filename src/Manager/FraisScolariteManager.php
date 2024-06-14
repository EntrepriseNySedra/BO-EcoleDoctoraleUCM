<?php

namespace App\Manager;
use App\Entity\AnneeUniversitaire;
use App\Entity\BankCompte;
use App\Entity\CalendrierPaiment;
use App\Entity\Ecolage;
Use App\Entity\FraisScolarite;
use App\Entity\Inscription;
use App\Entity\Mention;
Use App\Entity\Semestre;

Use App\Manager\AnneeUniversitaireManager;
Use App\Manager\EcolageManager;
Use App\Manager\SemestreManager;

Use App\Services\AnneeUniversitaireService;
Use App\Services\EcolageService;

Use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class FraisScolariteManager
 *
 * @package App\Manager
 */
class FraisScolariteManager extends BaseManager
{

    /**
     * Save student FS
     * @param FraisScolarite $fraisScolarite, EcolageService $ecoService
     * @return boolean
     */
    public function savePerEtudiant(FraisScolarite $fraisScolarite, EcolageService $ecoService): bool {
        $anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $ecoManager         = new EcolageManager($this->em, Ecolage::class);
        $anneeUniv = $anneeUnivService->getCurrent();
        $fraisScolarite->setAnneeUniversitaire($anneeUniv);
        // $fraisScolarite->setStatus(FraisScolarite::STATUS_SRS_VALIDATED);
        $ecolageFilters = [
                            'mention' => $fraisScolarite->getMention(), 
                            'niveau' => $fraisScolarite->getNiveau(),
                            'semestre' => $fraisScolarite->getSemestre()
                        ];
        if($parcours = $fraisScolarite->getParcours())
            $ecolageFilters['parcours'] = $parcours;
        $classTrancheEcolage = $ecoManager->loadOneBy($ecolageFilters, ['limit_date' => 'ASC']);
        //get student current tranche total
        $etudiant = $fraisScolarite->getEtudiant();
        $qb = $this->getRepository()->createQueryBuilder('fs')
            ->where('fs.etudiant = :pEtudiant')
            ->AndWhere('fs.status != :pStatus')
            ->AndWhere('fs.semestre = :pSemestre');
        if($fraisScolarite->getId())
            $qb = $qb->AndWhere('fs.id != :pId');

        $qb =  $qb->setParameter('pEtudiant', $etudiant)
            ->setParameter('pStatus', FraisScolarite::STATUS_SRS_REFUSED)
            ->setParameter('pSemestre', $fraisScolarite->getSemestre());

        if($fraisScolarite->getId())
            $qb =  $qb->setParameter('pId', $fraisScolarite->getId());
        $qb = $qb->getQuery();
        $etudiantTrancheEco = $qb->getResult();
        // $etudiantTrancheEco = $this->loadBy(
        //     [
        //         'etudiant' => $etudiant, 
        //         'semestre' => $fraisScolarite->getSemestre()
        //     ]
        // );        
        $totalTranche = array_sum(array_map(function($item){
            return $item->getMontant();
        }, $etudiantTrancheEco));
        //tranche rest to pay
        // if($fraisScolarite->getId())
        //     $trancheRest = $classTrancheEcolage->getMontant() - $totalTranche;
        // else
        $trancheRest = ($classTrancheEcolage ? $classTrancheEcolage->getMontant() : 0)  - $totalTranche - $fraisScolarite->getMontant();
        $fraisScolarite->setReste($trancheRest);
        // /dump($trancheRest);die;
        $this->persist($fraisScolarite);
        $this->flush();

        return true;
    }
    
    // public function savePerEtudiant(FraisScolarite $fraisScolarite, EcolageService $ecoService): bool {
    //     $anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
    //     $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
    //     $ecoManager         = new EcolageManager($this->em, Ecolage::class);
    //     $anneeUniv = $anneeUnivService->getCurrent();
    //     $fraisScolarite->setAnneeUniversitaire($anneeUniv);
    //     $fraisScolarite->setStatus(FraisScolarite::STATUS_SRS_VALIDATED);
    //     $ecolageFilters = ['mention' => $fraisScolarite->getMention(), 'niveau' => $fraisScolarite->getNiveau()];
    //     if($parcours = $fraisScolarite->getParcours())
    //         $ecolageFilters['parcours'] = $parcours;
    //     $classEcolage = $ecoManager->loadBy($ecolageFilters, ['limit_date' => 'ASC']);
    //     $ecoService->classEcolage = array_map(function(Ecolage $ecolage){
    //         return $ecolage;
    //     }, $classEcolage);
    //     //get student current total
    //     $etudiant = $fraisScolarite->getEtudiant();
    //     $etudiantEcoSem1 = $this->loadBy(['etudiant' => $etudiant, 'semestre' => $classEcolage[0]->getSemestre()]);
    //     $etudiantEcoSem2 = $this->loadBy(['etudiant' => $etudiant, 'semestre' => $classEcolage[1]->getSemestre()]);
    //     //check student eco sem 1 
    //     $totalEcoSem1 = array_sum(array_map(function($item){
    //         return $item->getMontant();
    //     }, $etudiantEcoSem1));
    //     $totalEcoSem2 = array_sum(array_map(function($item){
    //         return $item->getMontant();
    //     }, $etudiantEcoSem2));        
    //     //sem1 rest to pay
    //     $sem1Rap = $classEcolage[0]->getMontant() - $totalEcoSem1;
    //     $sem2Rap = $classEcolage[1]->getMontant() - $totalEcoSem2;
    //     if($totalEcoSem2 > 0 && $sem2Rap <= 0)
    //         return true;
    //     if($sem1Rap == 0){
    //         $fraisScolarite->setReste($classEcolage[1]->getMontant() - $totalEcoSem2 - $fraisScolarite->getMontant());
    //         $fraisScolarite->setSemestre($classEcolage[1]->getSemestre());
    //         $this->persist($fraisScolarite);
    //         $this->flush();
    //         return true;
    //     }
    //     if(($reste=($sem1Rap - $fraisScolarite->getMontant())) >= 0) {
    //         $fraisScolarite->setReste($reste);
    //         $fraisScolarite->setSemestre($classEcolage[0]->getSemestre());
    //         $this->persist($fraisScolarite);
    //     } else {
    //         $valueToSem2 = $fraisScolarite->getMontant() - $sem1Rap;
    //         $fraisScolarite->setMontant($sem1Rap);
    //         $fraisScolarite->setReste(0);
    //         $fraisScolarite->setSemestre($classEcolage[0]->getSemestre());
    //         $this->persist($fraisScolarite);

    //         $fraisScolariteClone = clone($fraisScolarite);
    //         $currentEcoSem2 = $totalEcoSem2 + $valueToSem2;
    //         $fraisScolariteClone->setMontant($currentEcoSem2);
    //         $fraisScolariteClone->setReste($classEcolage[1]->getMontant() - $currentEcoSem2);
    //         $fraisScolariteClone->setSemestre($classEcolage[1]->getSemestre());
    //         $this->persist($fraisScolariteClone);
    //     }
    //     $this->flush();

    //     return true;
    // }



    /**
     * @param int $semesterId
     * @param int $mentionId
     * @param int $niveauId
     * @param int $parcoursId
     * @param int $unnivId
     * 
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getListPerClass(
        $semesterId,
        $mentionId,
        $niveauId,
        $parcoursId,
        int $anneeUnivId 
    )
    {
        $sqlPacoursFilter = $parcoursId ? "AND fs.parcours_id = :parcoursId" : "";
        $sql = "
            SELECT *, (ecolage - montant_paye) as reste
            FROM (
                    SELECT et.id as etudiantId, et.first_name, et.last_name, SUM(fs.montant) as montant_paye, sem.libelle as semestre, sem.ecolage as ecolage, GROUP_CONCAT(fs.status SEPARATOR ',') as list_status
                    FROM frais_scolarite fs
                    INNER JOIN etudiant et ON et.id = fs.etudiant_id
                    INNER JOIN semestre sem ON sem.id = fs.semestre_id
                    WHERE fs.semestre_id = :semestreId
                    AND fs.mention_id = :mentionId
                    AND fs.niveau_id = :niveauId
                    $sqlPacoursFilter
                    AND fs.annee_universitaire_id = :anneUnivId
                    GROUP BY et.id
                    ORDER BY et.last_name ASC
                ) tFs
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('semestreId', $semesterId, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);
        $statement->bindParam('anneUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    public function setReste(&$fraisScol, $lastEcolage, $classEcolage) {
        if($lastEcolage && $reste=$lastEcolage->getReste()){
            $fraisScol->setReste($reste - $fraisScol->getMontant());
        } else {
            $fraisScol->setReste($classEcolage - $fraisScol->getMontant());
        }
    }

    /**
     * Get current period list
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function getCurrentPaiement(int $anneeUnivId, $calPaiement=null, $options=[]) {
        $optionFilters = $calPaiement ? " AND fs.date_paiement BETWEEN :pCalDateDebut AND :pCalDateFin ":"";
        $optionFilters .= !empty($options['semestre']) ? " AND fs.semestre_id = :semestre ":"";
        $optionFilters .= !empty($options['mention']) ? " AND fs.mention_id = :mention ":"";
        $optionFilters .= !empty($options['niveau']) ? " AND fs.niveau_id = :niveau ":"";
        $optionFilters .= !empty($options['parcours']) ? " AND fs.parcours_id = :parcours ":"";
        $optionFilters .= !empty($options['status']) ? " AND fs.status = :status ":"";
        $oldEcoGeMenNum  = !empty($parameterBag=$options['parameterBag']) ?  $parameterBag->get('num_compte_old_ecoge') : '';

        $sql = "
            SELECT et.id as etudiantId, et.first_name, et.last_name, et.immatricule, fs.id as ecolageId, fs.montant, fs.reste, fs.date_paiement, fs.remitter, fs.reference, fs.status, fs.mode_paiement, sem.libelle as semestre, sem.ecolage as ecolage, men.nom as mention, niv.libelle as niveau, parc.nom as parcours, fs.reference, fs.remitter, fs.updated_at as date_saisie, eco.montant as montant_tranche, IF(SUBSTR(et.immatricule,1,3) = :oldEcoGeMenPrefix, :oldEcoGeMenNum,  bc.number) as compte_number, SUBSTR(et.immatricule, 1, 3)
            FROM frais_scolarite fs
            INNER JOIN etudiant et ON et.id = fs.etudiant_id
            INNER JOIN mention men ON men.id = fs.mention_id
            INNER JOIN niveau niv ON niv.id = fs.niveau_id
            LEFT JOIN parcours parc ON parc.id = fs.parcours_id
            INNER JOIN semestre sem ON sem.id = fs.semestre_id
            INNER JOIN ecolage eco ON eco.semestre_id = fs.semestre_id AND eco.mention_id = fs.mention_id AND eco.niveau_id = fs.niveau_id AND (eco.parcours_id = fs.parcours_id OR eco.parcours_id IS NULL)
            LEFT JOIN 
                bank_compte bc ON bc.mention_id = fs.mention_id 
                AND (bc.niveau_id = fs.niveau_id OR bc.niveau_id IS NULL) 
                AND (bc.parcours_id = fs.parcours_id OR bc.parcours_id IS NULL) 
                AND bc.resource = :bcResource
            WHERE fs.annee_universitaire_id = :anneUnivId
            $optionFilters
            GROUP BY et.id, fs.montant, fs.reference, fs.updated_at
            ORDER BY fs.created_at DESC
        ";

        $bankCompteResource =  BankCompte::ECOLAGE;
        $oldEcoGeMenPrefix =  Mention::OLD_ECOGE_PREFIX;

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('oldEcoGeMenPrefix', $oldEcoGeMenPrefix, \PDO::PARAM_STR);
        $statement->bindParam('oldEcoGeMenNum', $oldEcoGeMenNum, \PDO::PARAM_STR);
        $statement->bindParam('bcResource', $bankCompteResource, \PDO::PARAM_STR);
        $statement->bindParam('anneUnivId', $anneeUnivId, \PDO::PARAM_INT);
        if($calPaiement){
            $calDateDebut = date_format($calPaiement->getDateDebut(), "Y-m-d");
            $calDateFin = date_format($calPaiement->getDateFin(), "Y-m-d");
            $statement->bindParam('pCalDateDebut', $calDateDebut, \PDO::PARAM_STR);
            $statement->bindParam('pCalDateFin', $calDateFin, \PDO::PARAM_STR);
        }
        if(!empty($options['semestre']) && $semestreId=$options['semestre'])
            $statement->bindParam('semestre', $semestreId, \PDO::PARAM_INT);
        if(!empty($options['mention']) && $mentionId=$options['mention'])
            $statement->bindParam('mention', $mentionId, \PDO::PARAM_INT);
        if(!empty($options['niveau']) && $niveauId=$options['niveau'])
            $statement->bindParam('niveau', $niveauId, \PDO::PARAM_INT);
        if(!empty($options['parcours']) && $parcoursId=$options['parcours'])
            $statement->bindParam('parcours', $parcoursId, \PDO::PARAM_INT);
        if(!empty($options['status']) && $status=$options['status'])
            $statement->bindParam('status', $status, \PDO::PARAM_INT);

        // dump($statement);die;
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Get current vente
     * @param anneeUnivId
     * @return mixed data
     */
    public function getCurrentVente(int $anneeUnivId, $options=[]) {

        $optionFilters = !empty($options['mention']) ? " AND ec.mention_id = :mention ":"";
        $optionFilters .= !empty($options['niveau']) ? " AND ec.niveau_id = :niveau ":"";
        $optionFilters .= !empty($options['parcours']) ? " AND ec.parcours_id = :parcours ":"";  

        $bankCompteFraisScolResource =  BankCompte::FRAIS_SCOLARITE;
        $bankCompteEcoResource =  BankCompte::ECOLAGE;
        $oldEcoGeMenPrefix =  Mention::OLD_ECOGE_PREFIX;

        $inscriptionValidated = Inscription::STATUS_VALIDATED;
        $sql = "
            SELECT * FROM (
            SELECT i.payement_ref, payement_ref_date, et.id as etudiantId, CONCAT(et.first_name, ' ', et.last_name) as etudiant, et.mention_id, SUM(ec.montant) as ecolage, men.nom as mention, men.id as mentionId, niv.id as niveauId, niv.libelle as niveau, parc.id as parcoursId, parc.nom as parcours, et.immatricule, et.last_name, et.first_name
            FROM inscription i
            INNER JOIN etudiant et ON et.id = i.etudiant_id
            INNER JOIN ecolage ec ON ec.mention_id = et.mention_id AND ec.niveau_id = et.niveau_id AND (ec.parcours_id IS NULL OR ec.parcours_id = et.parcours_id)
            INNER JOIN mention men ON men.id = et.mention_id
            INNER JOIN niveau niv ON niv.id = et.niveau_id
            LEFT JOIN parcours parc ON parc.id = et.parcours_id
            WHERE i.annee_universitaire_id = :anneeUnivId
            AND i.status = :incriptStatus
            $optionFilters
            GROUP BY etudiantId
            ORDER BY men.nom, niv.libelle, parc.nom, et.first_name, et.last_name) X 
            LEFT JOIN bank_compte bc ON bc.mention_id = X.mentionId AND (bc.niveau_id = X.niveauId OR bc.niveau_id IS NULL) 
                AND (bc.parcours_id = X.parcoursId OR bc.parcours_id IS NULL) 
            ORDER BY X.mention, X.niveau, X.parcours, X.first_name, X.last_name, bc.resource ASC
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('incriptStatus', $inscriptionValidated, \PDO::PARAM_STR);
        if(!empty($options['mention']) && $mentionId=$options['mention'])
            $statement->bindParam('mention', $mentionId, \PDO::PARAM_INT);
        if(!empty($options['niveau']) && $niveauId=$options['niveau'])
            $statement->bindParam('niveau', $niveauId, \PDO::PARAM_INT);
        if(!empty($options['parcours']) && $parcoursId=$options['parcours'])
            $statement->bindParam('parcours', $parcoursId, \PDO::PARAM_INT);

        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * Search by name
     * @param $statut STATUT_CREATED|STATUS_ASSIST_VALIDATED|STATUS_CM_VALIDATED
     * @return mixed data
     */
    public function searchByName(int $_anneeUnivId, $_qString) {
        
        $srsValidateStatus = FraisScolarite::STATUS_SRS_VALIDATED;
        $createdStatus = FraisScolarite::STATUS_CREATED;
        
        $sql = "
            SELECT et.id as etudiantId, et.first_name, et.last_name, et.immatricule, fs.id as ecolageId, fs.montant, fs.reste, fs.date_paiement, fs.remitter, fs.reference, fs.status, fs.mode_paiement, sem.libelle as semestre, sem.ecolage as ecolage, men.nom as mention, niv.libelle as niveau, parc.nom as parcours, fs.reference, fs.remitter, fs.updated_at as date_saisie, eco.montant as montant_tranche
            FROM frais_scolarite fs
            INNER JOIN etudiant et ON et.id = fs.etudiant_id
            INNER JOIN mention men ON men.id = fs.mention_id
            INNER JOIN niveau niv ON niv.id = fs.niveau_id
            LEFT JOIN parcours parc ON parc.id = fs.parcours_id
            INNER JOIN semestre sem ON sem.id = fs.semestre_id
            INNER JOIN ecolage eco ON eco.semestre_id = fs.semestre_id AND eco.mention_id = fs.mention_id AND eco.niveau_id = fs.niveau_id AND (eco.parcours_id = fs.parcours_id OR eco.parcours_id IS NULL)
            
            WHERE fs.annee_universitaire_id = :pAnneUnivId
            AND 
                (
                    (CONCAT(COALESCE(et.last_name), COALESCE(et.first_name)) LIKE '%" .  preg_replace('/\s+/', '', $_qString) . "%')
                    OR
                    (CONCAT(COALESCE(et.first_name), COALESCE(et.last_name)) LIKE '%" .  preg_replace('/\s+/', '', $_qString) . "%')
                )
            AND fs.status IN ($srsValidateStatus, $createdStatus)
            GROUP BY et.id, fs.montant, fs.reference, fs.updated_at
            ORDER BY fs.created_at DESC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneUnivId', $_anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

}