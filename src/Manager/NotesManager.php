<?php

namespace App\Manager;

use App\Entity\Etudiant;

/**
 * Class NotesManager
 *
 * @package App\Manager
 */
class NotesManager extends BaseManager
{

    /**
     * @param int $studentId
     * @param int $semesterId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByStudentAndSemesterId(Etudiant $student, int $semesterId)
    {
        $studentId = $student->getId();
        $mentionId = $student->getMention() ? $student->getMention()->getId() : 0;
        $niveauId = $student->getNiveau() ? $student->getNiveau()->getId() : 0;
        $parcoursId = $student->getParcours() ? $student->getParcours()->getId() : 0;
        //$parcoursSqlFilter = "";
        //if($parcoursId) $parcoursSqlFilter = "AND `ue.parcours_id` = :parcoursId";

        $parcoursSqlFilter = "";
        if ($parcoursId) {
            $parcoursSqlFilter = "AND `ue`.`parcours_id` = :parcoursId";
        }


        // dump($mention->getId());die;

        $sql = "
            SELECT id, unite_enseignements_id, nom AS matiere_nom, credit AS matiere_credit, volume_horaire_total, responsable AS ue_responsable, libelle AS ue_libelle, note, enseignant_name
            FROM (
                SELECT m.id, m.unite_enseignements_id, m.`nom`, m.`credit`, `m`.`volume_horaire_total`, ue.libelle, `ue`.`responsable`, IF(n.`rattrapage`, n.`rattrapage`, `n`.`note`) note,  CONCAT(`en`.`first_name`, `en`.`last_name`) enseignant_name FROM matiere m
                INNER JOIN `unite_enseignements` ue ON `ue`.id = `m`.`unite_enseignements_id`
                INNER JOIN `semestre` s ON `s`.`id` = `ue`.`semestre_id`
                LEFT JOIN `enseignant` en ON `en`.`id` = `m`.`enseignant_id`
                LEFT JOIN `notes` n ON `m`.`id` = `n`.`matiere_id`
                LEFT JOIN `etudiant` et ON `et`.`id` = `n`.`etudiant_id` OR n.id IS NULL
                WHERE (
                    `et`.`id` = :studentId
                    AND `s`.`id` = :semesterId
                    AND `ue`.`mention_id`= :mentionId
                    AND `ue`.`niveau_id`= :niveauId
                    $parcoursSqlFilter
                )
                -- OR n.id IS NULL
                -- UNION
                -- SELECT m.id, m.unite_enseignements_id, m.`nom`, m.`credit`, `m`.`volume_horaire_total`, ue.libelle, `ue`.`responsable`, NULL AS note,  CONCAT(`en`.`first_name`, `en`.`last_name`) enseignant_name FROM matiere m
                -- INNER JOIN `unite_enseignements` ue ON `ue`.id = `m`.`unite_enseignements_id`
                -- INNER JOIN `semestre` s ON `s`.`id` = `ue`.`semestre_id`
                -- LEFT JOIN `enseignant` en ON `en`.`id` = `m`.`enseignant_id`
                -- WHERE `s`.`id` = :semesterId
            ) AS tmp
            GROUP BY id
            ORDER BY unite_enseignements_id ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('studentId', $studentId, \PDO::PARAM_INT);
        $statement->bindParam('semesterId', $semesterId, \PDO::PARAM_INT);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($parcoursId) $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);;
        
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param int $enseignementId
     * @param int $matiereId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMatiereEnseignant(int $enseignantId, int $matiereId, int $anneeUnivId = 0)
    {
        $sql = "
            SELECT s.libelle as semestre, CONCAT(COALESCE(`et`.`last_name`, '') , ' ', COALESCE(`et`.`first_name`, '')) etudiant_name, et.`id` etudiantId, n.`note` as note, n.`rattrapage` as rattrapage 
            
            from etudiant et
            join mention men on men.id = et.mention_id
            join niveau niv on niv.id = et.niveau_id
            left join parcours parc on parc.id = et.parcours_id
            join unite_enseignements ue on ue.mention_id = men.id AND ue.niveau_id = niv.id AND (ue.parcours_id = parc.id OR ue.parcours_id IS NULL)
            join semestre s on s.id = ue.semestre_id
            join matiere m on m.unite_enseignements_id = ue.id
            left join notes n on n.matiere_id = m.id and n.etudiant_id = et.id AND n.annee_universitaire_id = :anneeUnivId
            left join enseignant en on en.id = m.enseignant_id    

            WHERE `en`.`id` = :enseignantId 
            AND m.id = :matiereId

            order by etudiant_name asc
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('matiereId', $matiereId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param int $etudiantId
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getEtudiantReleve(int $etudiantId, int $semesterId)
    {
        $sql = "
            SELECT s.libelle as semestre,ue.libelle as ueNom, ue.type as ueType, m.nom as matiere, m.credit as matiereCredit, IF(n.`rattrapage`, n.`rattrapage`, `n`.`note`) note
            FROM etudiant et
            JOIN mention men on men.id = et.mention_id
            JOIN niveau niv on niv.id = et.niveau_id
            JOIN unite_enseignements ue on ue.mention_id = men.id AND ue.niveau_id = niv.id
            JOIN semestre s on s.id = ue.semestre_id
            JOIN matiere m on m.unite_enseignements_id = ue.id
            LEFT JOIN notes n on n.matiere_id = m.id and n.etudiant_id = et.id
            WHERE et.id = :etudiantId AND s.id = :semesterId
            AND m.active = 1
            AND ue.active = 1
            ORDER BY ue.type ASC, s.libelle ASC;
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('etudiantId', $etudiantId, \PDO::PARAM_INT);
        $statement->bindParam('semesterId', $semesterId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

    /**
     * @param int $mentionId
     * @param int $niveauId
     * @param int $parcoursId
     * 
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getClassNotes(int $mentionId, $niveauId, $parcoursId, int $anneeUnivId, int $etudiantId=null)
    {
        $sqlPacoursFilter = $parcoursId ? "AND ue.parcours_id = :parcoursId" : "";
        $sqlEtudiantFilter = $etudiantId ? "AND et.id = :etudiantId" : "AND et.id IS NOT NULL";
        $sql = "
            SELECT et.id as etudiantId, et.first_name, et.last_name, m.id as matiereId, m.credit as matiereCredit, n.id as noteid, n.note, m.nom, m.code as codeEc, ue.libelle as ueLibelle, ue.type as ueType, ue.parcours_id, ue.mention_id, ue.credit as ueCredit, niv.code as niveau, sem.id as semestreId, sem.libelle as semestre
                FROM matiere m 
                LEFT JOIN notes n on n.matiere_id = m.id
                INNER JOIN unite_enseignements ue on ue.id = m.unite_enseignements_id 
                INNER JOIN semestre sem ON sem.id = ue.semestre_id
                LEFT JOIN etudiant et ON et.id = n.etudiant_id
                INNER JOIN niveau niv ON niv.id = et.niveau_id

            WHERE ue.mention_id = :mentionId 
            AND ue.niveau_id = :niveauId 
            $sqlPacoursFilter
            AND (n.annee_universitaire_id = :anneeUnivId OR n.annee_universitaire_id IS NULL)
            $sqlEtudiantFilter
            ORDER BY n.etudiant_id, sem.id, m.code;
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($parcoursId)
            $statement->bindParam('parcoursId', $parcoursId, \PDO::PARAM_INT);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();

        return $statement->fetchAll();
    }

}