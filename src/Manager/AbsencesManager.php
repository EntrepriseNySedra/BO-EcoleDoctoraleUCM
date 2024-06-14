<?php

namespace App\Manager;
use App\Entity\Etudiant;
use App\Entity\Inscription;

/**
 * Class CoursManager
 *
 * @package App\Manager
 */
class AbsencesManager extends BaseManager
{
    /**
     * Get Etudiant absence per matiere
     *
     * @param int $anneeUnivId
     * @param $options[] etudiant class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPerETudiantByMatiere(int $_anneeUnivId, $_etudiantId, $_matiereId) {
        $inscritStat = Inscription::STATUS_VALIDATED;
        $sql = "
            SELECT DISTINCT e.id as etudiantId, 
                (SELECT status FROM inscription i WHERE i.etudiant_id = :pEtudiantId AND i.annee_universitaire_id = :pAnneeuniv LIMIT 1) as insStatus, 
                mat.nom as matiere, 
                mat.id as matId, 
                edt.id as edtId, 
                edt.date_schedule, 
                edt.start_time, 
                edt.end_time, 
                a.id as absenceId, 
                IF(a.justification, 'Justifiée', 'Crée') as justification
            FROM etudiant e
            INNER JOIN absences a ON a.etudiant_id = e.id
            LEFT JOIN emploi_du_temps edt ON edt.id = a.emploi_du_temps_id
            LEFT JOIN matiere mat ON mat.id = edt.matiere_id
            WHERE e.id = :pEtudiantId
            AND mat.id = :pMatiereId
            HAVING insStatus = '$inscritStat'
            ORDER BY a.date, a.start_time
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pEtudiantId', $_etudiantId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeuniv', $_anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMatiereId', $_matiereId, \PDO::PARAM_INT);
        
        
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }


    /**
     * Get Etudiant absence per matiere
     *
     * @param int $anneeUnivId
     * @param $options[] etudiant class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPerETudiantByMaquette(int $_anneeUnivId, $_etudiantId, $_semestreId) {
        $queryFilter = $_semestreId ? " AND ue.semestre_id = :pSemestre " : "";
        $inscritStat = Inscription::STATUS_VALIDATED;
        $sql = "
            SELECT mat.nom as matiere, mat.id as matId, edt.id as edtId, a.id as absId, COUNT(mat.id) as nbrAbsence, a.etudiant_id as etudiantId
            FROM absences a
            LEFT JOIN emploi_du_temps edt ON edt.id = a.emploi_du_temps_id
            LEFT JOIN matiere mat ON mat.id = edt.matiere_id
            LEFT JOIN unite_enseignements ue ON ue.id = mat.unite_enseignements_id

            WHERE a.etudiant_id = :pEtudiantId
            AND edt.annee_universitaire_id = :pAnneeuniv
            $queryFilter
            GROUP BY mat.id
        ";
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pEtudiantId', $_etudiantId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeuniv', $_anneeUnivId, \PDO::PARAM_INT);
        if($_semestreId)
            $statement->bindParam('pSemestre', $_semestreId, \PDO::PARAM_INT);
        
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * Get Absence per Class
     *
     * @param int $anneeUnivId
     * @param $options[] etudiant class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPerClass(int $anneeUnivId, $options=[]) {
        $inscritStat = Inscription::STATUS_VALIDATED;
        $queryFilter = $options['parcours'] ? "AND e.parcours_id = :pParcours" : "";
        //$queryFilter = $options['matiere'] ? " AND edt.matiere_id = :pMatiere" : "";
        $sql = "
            SELECT 
            X.*, 
            COUNT(X.absId) as nbrAbsence, 
            COUNT(CASE WHEN X.absJustification IS NOT NULL THEN X.absId END) as nbrJustifiedAbsence
            FROM (
                SELECT DISTINCT 
                    e.id as etudiantId, 
                    e.first_name, 
                    e.last_name, 
                    a.emploi_du_temps_id,
                    (SELECT status FROM inscription i 
                        WHERE i.etudiant_id = e.id 
                        AND i.annee_universitaire_id = :pAnneeuniv 
                        LIMIT 1
                    ) as insStatus, 
                    a.id as absId,
                    a.justification as absJustification
                FROM etudiant e
                LEFT JOIN absences a ON a.etudiant_id = e.id
                LEFT JOIN emploi_du_temps edt ON a.emploi_du_temps_id = edt.id
                WHERE e.mention_id = :pMention
                AND e.niveau_id = :pNiveau
                $queryFilter
                HAVING insStatus = '$inscritStat'
            ) X
            GROUP BY X.etudiantId
            ORDER BY X.first_name ASC, X.last_name ASC
        ";
        $mentionId = $options['mention'];
        $niveauId = $options['niveau'];
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneeuniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $niveauId, \PDO::PARAM_INT);
        if($options['parcours']) $statement->bindParam('pParcours', $options['parcours'], \PDO::PARAM_INT);
        //if($options['matiere']) $statement->bindParam('pMatiere', $options['matiere'], \PDO::PARAM_INT);
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * Get Absence per Class
     *
     * @param int $anneeUnivId
     * @param $options[] etudiant class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPerClass_scol(int $anneeUnivId, $options=[]) {
        $inscritStat = Inscription::STATUS_VALIDATED;
        $queryFilter = "";
        $queryFilter .= $options['parcours'] ? "AND e.parcours_id = :pParcours" : "";
        $queryFilter .= $options['matiere'] ? " AND edt.matiere_id = :pMatiere" : "";
        $queryFilter .= $options['semestre'] ? " AND ue.semestre_id = :pSemestre" : "";
        $sql = "
            SELECT 
            X.*, 
            COUNT(X.absId) as nbrAbsence, 
            COUNT(CASE WHEN X.absJustification IS NOT NULL THEN X.absId END) as nbrJustifiedAbsence
            FROM (
                SELECT DISTINCT 
                    e.id as etudiantId, 
                    e.first_name, 
                    e.last_name, 
                    a.emploi_du_temps_id,
                    (SELECT status FROM inscription i 
                        WHERE i.etudiant_id = e.id 
                        AND i.annee_universitaire_id = :pAnneeuniv 
                        LIMIT 1
                    ) as insStatus, 
                    a.id as absId,
                    a.justification as absJustification
                FROM etudiant e
                LEFT JOIN absences a ON a.etudiant_id = e.id
                LEFT JOIN emploi_du_temps edt ON a.emploi_du_temps_id = edt.id

                LEFT JOIN matiere mat on mat.id = edt.matiere_id
                LEFT JOIN unite_enseignements ue on ue.id = mat.unite_enseignements_id 
                -- (AND e.mention_id = ue.mention_id AND e.niveau_id = ue.niveau_id AND e.parcours_id = ue.parcours_id)
                -- LEFT JOIN semestre sem on sem.id = ue.semestre_id
                WHERE e.mention_id = :pMention
                AND e.niveau_id = :pNiveau
                $queryFilter
                -- AND (ue.parcours_id IS NULL OR (ue.parcours_id = e.parcours_id))
                HAVING insStatus = '$inscritStat'
            ) X
            GROUP BY X.etudiantId
            ORDER BY X.last_name ASC, X.first_name ASC
        ";

        $mentionId = $options['mention'];
        $niveauId = $options['niveau'];
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneeuniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $niveauId, \PDO::PARAM_INT);
        if($options['parcours']) $statement->bindParam('pParcours', $options['parcours'], \PDO::PARAM_INT);
        if($options['matiere']) $statement->bindParam('pMatiere', $options['matiere'], \PDO::PARAM_INT);
        if($options['semestre']) $statement->bindParam('pSemestre', $options['semestre'], \PDO::PARAM_INT);
        $statement->executeQuery();

        // dd($statement);
        $result = $statement->fetchAll();

        return $result;
    }




    /**
     * Get Per Maquette
     *
     * @param int $anneeUnivId
     * @param $options[] class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAbsenceParMatiere(int $anneeUnivId, $options=[]) {
        $inscritStat = Inscription::STATUS_VALIDATED;
        $queryParcoursFilter = $options['parcours'] ? "AND a.parcours_id = :pParcours" : "";
        $sql = "
            SELECT 
            a.id as id,
            a.date as date,
            emp.start_time as start_time,
            emp.end_time as end_time,
            e.first_name as nom,
            e.last_name as prenom,
            m.nom AS mention_nom,
            n.libelle AS niveau_libelle,
            ma.nom AS matiere_nom, 
            p.nom as parcours_name
            FROM 
                absences a 
            LEFT JOIN 
                etudiant e ON e.id = a.etudiant_id 
            LEFT JOIN 
                mention m ON m.id = a.mention_id 
            LEFT JOIN 
                niveau n ON n.id = a.niveau_id 
            LEFT JOIN 
                parcours p ON p.id = a.parcours_id 
            JOIN 
                inscription i ON i.etudiant_id = e.id  
            LEFT JOIN 
                emploi_du_temps emp ON emp.id = a.emploi_du_temps_id
            LEFT JOIN 
                matiere ma ON ma.id = emp.matiere_id 
          WHERE 
            i.status= '$inscritStat' and
            a.mention_id = :pMention
            AND a.niveau_id = :pNiveau
            AND a.annee_universitaire_id = :pAnneeuniv 
            $queryParcoursFilter
            ORDER BY a.date DESC
        ";
        $mentionId = $options['mention'];
        $niveauId = $options['niveau'];
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneeuniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $niveauId, \PDO::PARAM_INT);
        if($options['parcours'])
            $statement->bindParam('pParcours', $options['parcours'], \PDO::PARAM_INT);
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }
    


    /**
     * Get Per Maquette
     *
     * @param int $anneeUnivId
     * @param $options[] class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMaquette(int $anneeUnivId, $options=[]) {
        $queryFilter = "";
        $queryFilter .= $options['parcours'] ? " AND ue.parcours_id = :pParcours " : "";
        $queryFilter .= $options['semestre'] ? " AND ue.semestre_id = :pSemestre " : "";
        $sql = "
                SELECT mat.id as matiere_id, mat.nom as matiere, ue.libelle as ue, (
                        SELECT COUNT(*) FROM absences a 
                        INNER JOIN emploi_du_temps edt ON edt.id = a.emploi_du_temps_id
                        WHERE edt.matiere_id = mat.id
                        AND edt.annee_universitaire_id = :pAnneeuniv
                    ) as nbr_absence
                FROM matiere mat
                INNER JOIN unite_enseignements ue ON ue.id = mat.unite_enseignements_id
                WHERE ue.mention_id = :pMention
                AND ue.niveau_id = :pNiveau
                $queryFilter
                ORDER BY ue.libelle ASC, mat.nom  ASC
        ";
        $mentionId = $options['mention'];
        $niveauId = $options['niveau'];
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneeuniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMention', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $niveauId, \PDO::PARAM_INT);
        if($options['parcours'])
            $statement->bindParam('pParcours', $options['parcours'], \PDO::PARAM_INT);
        if($options['semestre'])
            $statement->bindParam('pSemestre', $options['semestre'], \PDO::PARAM_INT);
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }


    /**
     * Get Per Matiere
     *
     * @param int $anneeUnivId
     * @param $options[] class params 
     *
     * @return array absences
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMatiere(int $anneeUnivId, $_matiereId) {
        
        $sql = "
            SELECT 
                mat.id as matiere_id, 
                mat.nom as matiere, 
                ue.libelle as ue, 
                edt.date_schedule, 
                a.etudiant_id, 
                etd.first_name, 
                etd.last_name, 
                COUNT(etd.id) as nbr_absence,
                SUM(CASE WHEN a.justification IS NOT NULL THEN 1 ELSE 0 END) as nbrAbsenceJustifie
            FROM matiere mat
            INNER JOIN unite_enseignements ue ON ue.id = mat.unite_enseignements_id
            INNER JOIN emploi_du_temps edt ON edt.matiere_id = mat.id AND edt.annee_universitaire_id = :pAnneeuniv
            INNER JOIN absences a ON edt.id = a.emploi_du_temps_id AND edt.annee_universitaire_id = :pAnneeuniv
            INNER JOIN etudiant etd ON etd.id = a.etudiant_id
            WHERE mat.id = :pMatiereId
            GROUP BY etd.id
            ORDER BY mat.nom, ue.libelle ASC
        ";

        
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pAnneeuniv', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('pMatiereId', $_matiereId, \PDO::PARAM_INT);
        
        $statement->executeQuery();
        $result = $statement->fetchAll();

        return $result;
    }
}