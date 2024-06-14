<?php

namespace App\Manager;

/**
 * Class CoursManager
 *
 * @package App\Manager
 */
class CoursManager extends BaseManager
{
    /**
     * Get Enseignant cours
     *
     * @param int $enseignantId
     * return cours[]
     * @param int $anneeUnivId
     *
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByEnseignant(int $enseignantId, int $anneeUnivId) {
        $sql = "
            SELECT m.id as matiereId, m.nom as matiereNom, ue.libelle as ueLibelle, niv.libelle as niveauLibelle, men.nom as mentionNom, parc.nom as parcoursNom, cou.libelle as coursLibelle, cou.id as coursId,cm.id as coursMediaId, cm.path as coursMediaPath, cm.name AS coursMediaName, cm.type as coursMediaType FROM matiere m
            INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
            INNER JOIN mention men ON men.id = ue.mention_id
            LEFT JOIN parcours parc ON parc.id = ue.parcours_id
            INNER JOIN niveau niv ON niv.id = ue.niveau_id
            INNER JOIN semestre sem ON sem.id = ue.semestre_id
            LEFT JOIN cours cou ON cou.matiere_id = m.id AND `cou`.`annee_universitaire_id` = :anneeUnivId 
            LEFT JOIN cours_media cm ON cm.cours_id = cou.id  
            
            WHERE m.enseignant_id = :enseignantId
            ORDER BY sem.libelle, mentionNom ASC, niveauLibelle ASC, ueLibelle ASC, matiereNom ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = [];
        //dump($statement);die;
        foreach($statement->fetchAll() as $item) {
            $mentionItem = [];
            $matiereNom = $item['matiereId'] . '-' . $item['matiereNom'];
            $coursLibelle = $item['coursId'] . '-' . $item['coursLibelle'];
            if(
               !array_key_exists($item['mentionNom'], $result)
               || !array_key_exists($item['niveauLibelle'], $result[$item['mentionNom']])
               || !array_key_exists($item['ueLibelle'], $result[$item['mentionNom']][$item['niveauLibelle']])
               || !array_key_exists($matiereNom, $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']])
               || !array_key_exists($coursLibelle, $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom])
            ) {
                if ($item['coursLibelle']) {
                    $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom][$coursLibelle] = [];
                } else {
                    $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom] = '';
                }
            }
            $mentionItem['parcoursNom'] = $item['parcoursNom'];
//            $mentionItem['niveauLibelle'] = $item['niveauLibelle'];
//            $mentionItem['matiereId'] = $item['matiereId'];
//            $mentionItem['matiereNom'] = $item['matiereNom'];
//            $mentionItem['ueLibelle'] = $item['ueLibelle'];
//            $mentionItem['coursLibelle'] = $item['coursLibelle'];
//            $mentionItem['coursId'] = $item['coursId'];
            $mentionItem['coursMediaId'] = $item['coursMediaId'];
            $mentionItem['coursMediaType'] = $item['coursMediaType'];
            $mentionItem['coursMediaPath'] = $item['coursMediaPath'];
            $mentionItem['coursMediaName'] = $item['coursMediaName'];
            if ($item['coursLibelle']) {
                array_push($result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom][$coursLibelle], $mentionItem);
            }
        }
        // dump($result);die;
        return $result;
    }

    /**
     * Get Matiere cours
     * @param int $matiereId
     * @param int $anneeUnivId
     * return cours[]
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByMatiere(int $matiereId, int $anneeUnivId) {
        $sql = "
            SELECT m.id as matiereId, m.nom as matiereNom, ue.libelle as ueLibelle, niv.libelle as niveauLibelle, men.nom as mentionNom, parc.nom as parcoursNom, cou.libelle as coursLibelle, cou.id as coursId,cm.id as coursMediaId, cm.path as coursMediaPath, cm.name AS coursMediaName, cm.type as coursMediaType FROM matiere m
            INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
            INNER JOIN mention men ON men.id = ue.mention_id
            LEFT JOIN parcours parc ON parc.id = ue.parcours_id
            INNER JOIN niveau niv ON niv.id = ue.niveau_id
            INNER JOIN semestre sem ON sem.id = ue.semestre_id
            LEFT JOIN cours cou ON cou.matiere_id = m.id AND `cou`.`annee_universitaire_id` = :anneeUnivId 
            LEFT JOIN cours_media cm ON cm.cours_id = cou.id  
            
            WHERE m.id = :matiereId
            ORDER BY sem.libelle, mentionNom ASC, niveauLibelle ASC, ueLibelle ASC, matiereNom ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('anneeUnivId', $anneeUnivId, \PDO::PARAM_INT);
        $statement->bindParam('matiereId', $matiereId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = [];
        //dump($statement);die;
        foreach($statement->fetchAll() as $item) {
            $mentionItem = [];
            $matiereNom = $item['matiereId'] . '-' . $item['matiereNom'];
            $coursLibelle = $item['coursId'] . '-' . $item['coursLibelle'];
            if(
               !array_key_exists($item['mentionNom'], $result)
               || !array_key_exists($item['niveauLibelle'], $result[$item['mentionNom']])
               || !array_key_exists($item['ueLibelle'], $result[$item['mentionNom']][$item['niveauLibelle']])
               || !array_key_exists($matiereNom, $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']])
               || !array_key_exists($coursLibelle, $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom])
            ) {
                if ($item['coursLibelle']) {
                    $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom][$coursLibelle] = [];
                } else {
                    $result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom] = '';
                }
            }
            $mentionItem['parcoursNom'] = $item['parcoursNom'];
            $mentionItem['coursMediaId'] = $item['coursMediaId'];
            $mentionItem['coursMediaType'] = $item['coursMediaType'];
            $mentionItem['coursMediaPath'] = $item['coursMediaPath'];
            $mentionItem['coursMediaName'] = $item['coursMediaName'];
            if ($item['coursLibelle']) {
                array_push($result[$item['mentionNom']][$item['niveauLibelle']][$item['ueLibelle']][$matiereNom][$coursLibelle], $mentionItem);
            }
        }
        //dump($result);die;
        return $result;
    }

}