<?php

namespace App\Manager;

use App\Entity\AnneeUniversitaire;
use App\Entity\Concours;
use App\Entity\ConcoursCandidature;
use Doctrine\ORM\Query;

/**
 * Class ConcoursCandidatureManager
 *
 * @package App\Manager
 */
class ConcoursCandidatureManager extends BaseManager
{

    /**
     * @param Concours $concours
     * @param array of candidates ID
     */
    public function setConcoursResult(Concours $concours, $candidateAdmittedIds=array()) {
        $allCandidates = $this->loadBy(
            [
                'anneeUniversitaire' => $concours->getAnneeUniversitaire(),
                'mention' => $concours->getMention(),
                'niveau'  => $concours->getNiveau(),
                'parcours'=> $concours->getParcours()
            ], []
        );
        foreach($allCandidates as $candidate) {
            $candidateResult = in_array($candidate->getId(), $candidateAdmittedIds);
            $candidate->setResultat($candidateResult);
            $this->persist($candidate);
        }

        $this->flush();
    }

    /**
     * @param array $criterias
     * @param null  $order
     *
     * @return mixed
     */
    public function findByCriteria(array $criterias = [], $order = null)
    {
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('cc')
           ->from($this->class, 'cc')
        ;

        foreach ($criterias as $key => $criteria) {
            $qb->andWhere('cc.' . $key . ' = :' . $key)->setParameter($key, $criteria);
        }

        return $qb->getQuery()->getResult();

    }

    /**
     * @param array $criterias
     * @param array $stringCriteria
     * @param null  $order
     *
     * @return mixed
     */
    public function findByMixedCriteria(array $criterias = [], array $stringCriteria, $order = null)
    {
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('cc')
           ->from($this->class, 'cc')
        ;

        foreach ($criterias as $key => $criteria) {
            $qb->andWhere('cc.' . $key . ' = :' . $key)->setParameter($key, $criteria);
        }
        foreach ($stringCriteria as $key => $sqlAndWhere) {
            //dump($sqlAndWhere);die;
            // $qb->andWhere($sqlAndWhere);
            $qb->andWhere('cc.' . $key . ' ' . $sqlAndWhere);
        }

        return $qb->getQuery()->getResult();    }

    public function isUnique(string $email, int $mentionId, int $collegeYearId, $refPaiement)
    {
        $sql = "
                SELECT COUNT(id) FROM `concours_candidature` cc
                WHERE `cc`.mention_id =  :pMentionId
                AND `cc`.email = :pEmail
                AND `cc`.`annee_universitaire_id` = :pCollegeYearId
            ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('pMentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pEmail', $email, \PDO::PARAM_STR);
        $statement->bindParam('pCollegeYearId', $collegeYearId, \PDO::PARAM_INT);
        $statement->executeQuery();
        
        return $this->checkRefPaiement($refPaiement) && ((int) $statement->fetchColumn() <= 0);
    }

    private function checkRefPaiement($refPaiement){
        $sql = "
                SELECT COUNT(id) FROM `concours_candidature` cc
                WHERE `cc`.payement_ref = :pRefPaiement";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('pRefPaiement', $refPaiement, \PDO::PARAM_STR);
        $statement->executeQuery();
        
        return ((int) $statement->fetchColumn() == 0);
    }

    /**
     * @param array $criterias
     * @param null  $order
     *
     * @return \Doctrine\ORM\Query
     */
    public function findByCriteriaQuery(array $criterias = [], $order = null): Query
    {
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('cc')
            ->from($this->class, 'cc')
        ;

        foreach ($criterias as $key => $criteria) {
            $qb->andWhere('cc.' . $key . ' = :' . $key)->setParameter($key, $criteria);
        }
        // $qb->groupBy('cc.mention');
        // $qb->groupBy('cc.firstName');
        // $qb->groupBy('cc.lastName');
        

        return $qb->getQuery();

    }

    public function findAllByAnneeUniv(AnneeUniversitaire $anneeUniv)
    {
        $_anneeUnivId = $anneeUniv->getId();
        $sql = "
                SELECT cc.id as candidatureId, cc.first_name, cc.last_name, cc.created_at, m.nom, cc.immatricule, m.diminutif FROM `concours_candidature` cc
                INNER JOIN mention m ON m.id = cc.mention_id
                WHERE `cc`.`annee_universitaire_id` = :pAnneeUnivId
                ORDER BY m.nom, cc.created_at
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('pAnneeUnivId', $_anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    public function findValidatedList($_mentionId, $_niveauId, $_parcoursId, $_anneeUnivId, $_qString){
        $_status = ConcoursCandidature::STATUS_SU_VALIDATED;
        $sql = "
                SELECT cc.id, cc.first_name, cc.last_name, cc.immatricule, cc.date_of_birth, cc.diplome, cc.email FROM `concours_candidature` cc
                WHERE `cc`.`annee_universitaire_id` = :pAnneeUnivId
                AND mention_id = :pMentionId
                AND niveau_id = :pNiveauId
                AND (:pBoolPArcours OR (parcours_id = :pParcouId))
                AND CONCAT( COALESCE(first_name, ''), COALESCE(last_name, '')) LIKE '%$_qString%'
                AND status = :pStatus
                ORDER BY cc.last_name ASC
        ";
        $bParcours = $_parcoursId ? false : true;
        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('pAnneeUnivId', $_anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMentionId', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveauId', $_niveauId, \PDO::PARAM_INT);
        $statement->bindParam('pBoolPArcours', $bParcours, \PDO::PARAM_BOOL);
        $statement->bindParam('pParcouId', $_parcoursId, \PDO::PARAM_INT);
        $statement->bindParam('pStatus', $_status, \PDO::PARAM_INT);
        $statement->executeQuery();
        // dump($statement);die;
        return $statement->fetchAll();
    }
}