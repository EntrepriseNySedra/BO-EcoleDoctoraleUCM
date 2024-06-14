<?php

namespace App\Manager;

use App\Entity\Concours;
use App\Entity\ConcoursCandidature;
use App\Entity\Mention;

/**
 * Class ConcoursNotesManager
 *
 * @package App\Manager
 */
class ConcoursNotesManager extends BaseManager
{

    /**
     * @param int $concoursCandidatureId
     * @param int $concoursId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByCandidateIdAndConcoursId(int $concoursCandidatureId, int $concoursId)
    {
        $sql = "
            SELECT `cm`.`id`, `cm`.`libelle`, `cn`.`note` 
            FROM `concours_matiere` cm
            LEFT JOIN `concours_notes` cn ON `cm`.`id` = `cn`.`concours_matiere_id`
            WHERE `cn`.`concours_candidature_id` = :concoursCandidatureId
            AND `cn`.`concours_id` = :concoursId
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('concoursCandidatureId', $concoursCandidatureId, \PDO::PARAM_INT);
        $statement->bindParam('concoursId', $concoursId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param array                                   $data
     * @param \App\Entity\ConcoursCandidature         $concoursCandidature
     * @param \App\Manager\ConcoursCandidatureManager $concoursCandidatureManager
     * @param \App\Manager\ConcoursMatiereManager     $concoursMatiereManager
     * @param \App\Manager\ConcoursManager            $concoursManager
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function manageNotes(
        array $data,
        ConcoursCandidature $concoursCandidature,
        ConcoursCandidatureManager $concoursCandidatureManager,
        ConcoursMatiereManager $concoursMatiereManager,
        ConcoursManager $concoursManager
    ) {
        $currentNotes = $this->loadBy(['concoursCandidature' => $concoursCandidature]);
        $matiereIds = [];
        foreach ($data as $name => $value) {
            if (preg_match('#^note_(\d+)_(\d+)_(\d+)#', $name, $matches)
                && !empty($matches[1])
                && !empty($matches[2])
                && !empty($matches[3])
            ) {
                $concoursMatiere     = $concoursMatiereManager->load($matches[2]);
                $concours            = $concoursManager->load($matches[3]);

                $matiereIds[] = $matches[2];

                /** @var \App\Entity\ConcoursNotes $notes */
                $notes = $this->loadOneBy(
                    [
                        'concoursCandidature' => $concoursCandidature,
                        'concoursMatiere'     => $concoursMatiere,
                        'concours'            => $concours,
                    ]
                );
                if (!$notes) {
                    $notes = $this->createObject();
                    $notes->setConcoursCandidature($concoursCandidature);
                    $notes->setConcoursMatiere($concoursMatiere);
                    $notes->setConcours($concours);
                }
                if(!$notes->getNote() && $value)
                    $notes->setNote(floatval( str_replace(',', '.', $value)));
                $this->persist($notes);
            }
        }

        /** @var \App\Entity\ConcoursNotes $note */
        foreach ($currentNotes as $note) {
            if (!in_array($note->getConcoursMatiere()->getId(), $matiereIds)) {
                $this->delete($note);
            }
        }

        $this->flush();
    }

    /**
     * @param \App\Entity\Concours $concours
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getResultByConcours(Concours $concours, Mention $mention, int $anneeUnivId = null, $parcoursId, $resultStatut = null)
    {
        $candidStatusApprove = ConcoursCandidature::STATUS_SU_VALIDATED;
        $concoursStatus = !$resultStatut ? Concours::STATUS_VALIDATED_RECTEUR : $resultStatut;
        $admittedResult = ConcoursCandidature::RESULT_ADMITTED;
        $sqlPacoursFilter = $parcoursId ? "AND `c`.`parcours_id` = :parcoursId" : "";
        $sql = "
                SELECT `cn`.`concours_candidature_id`, ROUND(AVG(`note`), 2) average, `cc`.`first_name`, `cc`.`last_name`, `cc`.`email`, `cc`.`date_of_birth`, `cc`.`immatricule`, `cct`.`name` as centre, `cc`.`confession_religieuse`
                FROM `concours_notes` cn
                INNER JOIN `concours_candidature` cc ON `cc`.`id` = `cn`.`concours_candidature_id`
                INNER JOIN `concours` c ON `c`.`id` = `cn`.`concours_id`
                LEFT JOIN `concours_centre` cct ON `cct`.`id` = `cc`.`centre_id`
                WHERE `c`.`id` = :concoursId
                AND `cc`.`mention_id` = :mentionId
                $sqlPacoursFilter
                AND cc.annee_universitaire_id = :anneeUnivId
                AND c.result_statut = :validatedDoyen
                -- AND cc.resultat = :admittedResult
                AND cc.status = :candidStatusApprove
                GROUP BY `cn`.`concours_candidature_id`
                HAVING average >= :deliberation
                ORDER BY `cc`.`immatricule` ASC
        ";
        $concoursId   = $concours->getId();
        $mentionId    = $mention->getId();
        $deliberation = $concours->getDeliberation();
        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('concoursId', $concoursId, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);    
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('validatedDoyen', $concoursStatus, \PDO::PARAM_INT);
        // $statement->bindParam('admittedResult', $admittedResult, \PDO::PARAM_INT);
        $statement->bindParam('candidStatusApprove', $candidStatusApprove, \PDO::PARAM_INT);
        $statement->bindParam('deliberation', $deliberation, \PDO::PARAM_STR);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param int                  $candidateId
     * @param \App\Entity\Concours $concours
     * @param \App\Entity\Mention  $mention
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCandidateResultByConcours(int $candidateId, Concours $concours, Mention $mention)
    {
        $candidStatusApprove = ConcoursCandidature::STATUS_APPROVED;
        $concoursStatus = Concours::STATUS_VALIDATED_RECTEUR;
        $sql = "
                SELECT `cn`.`concours_candidature_id`, ROUND(AVG(`note`), 2) average, `cc`.`first_name`, `cc`.`last_name`, `cc`.`email`, `cc`.`date_of_birth`,`c`.`deliberation`
                FROM `concours_notes` cn
                INNER JOIN `concours_candidature` cc ON `cc`.`id` = `cn`.`concours_candidature_id`
                INNER JOIN `concours` c ON `c`.`id` = `cn`.`concours_id`
                WHERE `c`.`id` = :concoursId
                AND `cn`.`concours_candidature_id` = :candidateId
                AND c.result_statut = :validatedRecteur
                AND `cc`.`status` = :candidStatusApprove
                GROUP BY `cn`.`concours_candidature_id`
                -- HAVING average >= :deliberation
                ORDER BY average DESC
        ";

        $concoursId   = $concours->getId();
        $mentionId    = $mention->getId();
        $deliberation = $concours->getDeliberation();
        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('concoursId', $concoursId, \PDO::PARAM_INT);
        $statement->bindParam('candidateId', $candidateId, \PDO::PARAM_INT);
        $statement->bindParam('validatedRecteur', $concoursStatus, \PDO::PARAM_INT);
        $statement->bindParam('candidStatusApprove', $candidStatusApprove, \PDO::PARAM_INT);
        // $statement->bindParam('deliberation', $deliberation, \PDO::PARAM_STR);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param \App\Entity\Concours $concours
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLevelNotesByMixedParams(
        Concours $concours, 
        Mention $mention, 
        int $anneeUnivId = null, 
        $parcoursId,
        $concoursResultStatus = null,
        $deliberation = null,
        $_qString = null,
        $result = null
    )
    {
        $candidStatusApprove = ConcoursCandidature::STATUS_SU_VALIDATED;
        $sqlStatusFilter = $concoursResultStatus ? " AND c.result_statut = :resultStatut " : " AND c.result_statut IS NOT NULL ";
        $sqlPacoursFilter = $parcoursId ? "AND `c`.`parcours_id` = :parcoursId" : "";
        $sqlDeliberationFilter = $deliberation ? "HAVING average >= :pDeliberation" : "";
        $sqlResultFilter = $result ? "AND cc.`resultat` = :pResultat" : "";
        $sql = "
                SELECT `cn`.`concours_candidature_id`, 
                IF(
                    (COUNT(cn.note) = (SELECT count(*) as nbr FROM concours_matiere cm WHERE cm.concours_id = c.id)), ROUND(AVG(cn.`note`), 2), 0
                )
                 as average, `cc`.`first_name`, `cc`.`last_name`, `cc`.`email`, `cc`.`date_of_birth`, `c`.`result_statut` as resultStatut, cc.resultat, `cc`.`immatricule`, `cct`.`name` as centre, `cc`.`confession_religieuse`
                FROM `concours_candidature` cc
                LEFT JOIN `concours_notes` cn ON `cc`.`id` = `cn`.`concours_candidature_id`
                LEFT JOIN `concours` c ON `c`.`id` = `cn`.`concours_id`
                LEFT JOIN `concours_centre` cct ON `cct`.`id` = `cc`.`centre_id`
                WHERE `c`.`id` = :concoursId
                AND `cc`.`mention_id` = :mentionId
                AND `cc`.`status` = :candidStatusApprove
                $sqlPacoursFilter
                AND cc.annee_universitaire_id = :anneeUnivId
                $sqlStatusFilter
                AND CONCAT( COALESCE(`cc`.first_name, ''), COALESCE(`cc`.last_name, '') ) LIKE '%$_qString%'
                $sqlResultFilter
                GROUP BY `cn`.`concours_candidature_id`
                $sqlDeliberationFilter
                ORDER BY average DESC
        ";
        
        $concoursId   = $concours->getId();
        $mentionId    = $mention->getId();
        //$deliberation = $concours->getDeliberation();
        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('concoursId', $concoursId, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('candidStatusApprove', $candidStatusApprove, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);    
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        if($concoursResultStatus)
            $statement->bindParam('resultStatut', $concoursResultStatus, \PDO::PARAM_INT);
        if($deliberation)
            $statement->bindParam('pDeliberation', $deliberation, \PDO::PARAM_INT);
        if($result)
            $statement->bindParam('pResultat', $result, \PDO::PARAM_INT);

        $statement->executeQuery();

        //dump($statement);die;

        return $statement->fetchAll();
    }

}
