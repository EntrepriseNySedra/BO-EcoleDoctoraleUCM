<?php

namespace App\Manager;

use Doctrine\ORM\Query\ResultSetMapping;
use App\Entity\Matiere;
use App\Entity\Enseignant;
use App\Entity\Mention;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
/**
 * Class MatiereManager
 *
 * @package App\Manager
 */
class MatiereManager extends BaseManager
{

    public function getCreditByUE()
    {
        $sql = "
            SELECT
              SUM(m.`credit`) as credit,
              m.`unite_enseignements_id` AS ue_id
            FROM
              matiere m
            GROUP BY `m`.`unite_enseignements_id`
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->executeQuery();

        $credits = [];
        foreach ($statement->fetchAll() as $item) {
            $credits[$item['ue_id']] = $item['credit'];
        }

        return $credits;
    }

    
    public function findAllEnseignant(): array
    {
        // Utilisation de l'EntityManager pour récupérer tous les objets Matiere
        return $this->em->getRepository(Enseignant::class)->findAll();
        //return $this->em->getRepository(Matiere::class)->findBy([], [], 40);
    }

    public function findAllMentions(): array
    {
        // Utilisation de l'EntityManager pour récupérer tous les objets Matiere
        return $this->em->getRepository(Mention::class)->findAll();
        //return $this->em->getRepository(Matiere::class)->findBy([], [], 40);
    }  

    public function findAllMatiereQuery(): \Doctrine\ORM\Query
    {
        $query = $this->em->createQuery('
            SELECT m, e, c, cm
            FROM App\Entity\Matiere m
            LEFT JOIN m.enseignant e
            LEFT JOIN m.cours c
            LEFT JOIN c.coursMedia cm
        ');

        return $query;
    }
    public function findAllMatiere(): array
    {
        $query = $this->em->createQuery('
            SELECT m, e, c, cm
            FROM App\Entity\Matiere m
            LEFT JOIN m.enseignant e
            LEFT JOIN m.cours c
            LEFT JOIN c.coursMedia cm
        ');

        return $query->getResult();
    }

    public function findMatiereByEnseignantName($nomprof): array
    {
        $query = $this->em->createQuery('
            SELECT m, e, c, cm
            FROM App\Entity\Matiere m
            LEFT JOIN m.enseignant e
            LEFT JOIN m.cours c
            LEFT JOIN c.coursMedia cm
            WHERE CONCAT(e.firstName, \' \', e.lastName) = :nomprof
        ');
        $query->setParameter('nomprof', $nomprof);

        return $query->getResult();
    }

    public function findMatiereByMention(string $mentionNom): array
    {
        $query = $this->em->createQuery('
            SELECT m
            FROM App\Entity\Matiere m
            JOIN m.uniteEnseignements ue
            JOIN ue.mention mention
            WHERE mention.nom = :mentionNom
        ');

        $query->setParameter('mentionNom', $mentionNom);

        return $query->getResult();
    }

    /**
     * Get Enseignant matiere 
     * @param $enseignantId
     * return cours[]
     */
    public function getByEnseignant(int $enseignantId) {
        $sql = "
            SELECT m.id as matiereId, m.nom as matiereNom, ue.libelle as ueLibelle, niv.libelle as niveauLibelle, men.nom as mentionNom, parc.nom as parcoursNom, sem.libelle as semestre  FROM matiere m

            INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
            INNER JOIN mention men ON men.id = ue.mention_id
            LEFT JOIN parcours parc ON parc.id = ue.parcours_id
            INNER JOIN niveau niv ON niv.id = ue.niveau_id
            INNER JOIN semestre sem ON sem.id = ue.semestre_id
            
            WHERE m.enseignant_id = :enseignantId
            GROUP BY m.id
            ORDER BY sem.libelle ASC, mentionNom ASC, niveauLibelle ASC, ueLibelle ASC, matiereNom ASC
            
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = [];
        foreach($statement->fetchAll() as $item) {
            $mentionItem = [];
            if(!array_key_exists($item['mentionNom'], $result)) {
                $result[$item['mentionNom']] = [];
            }
            $mentionItem['parcoursNom'] = $item['parcoursNom'];
            $mentionItem['niveauLibelle'] = $item['niveauLibelle'];
            $mentionItem['matiereId'] = $item['matiereId'];
            $mentionItem['matiereNom'] = $item['matiereNom'];
            $mentionItem['ueLibelle'] = $item['ueLibelle'];
            $mentionItem['semestre'] = $item['semestre'];
            array_push($result[$item['mentionNom']], $mentionItem);
        }
        
        return $result;
    }

    /**
     * Get Enseignant matiere 
     * @param $enseignantId
     * return cours[]
     */
    public function getGroupedByEnseignant(int $enseignantId) {
        $sql = "
            SELECT m.id as matiereId, m.nom as matiereNom, ue.libelle as ueLibelle, niv.libelle as niveauLibelle, men.nom as mentionNom, parc.nom as parcoursNom, sem.libelle as semestre  FROM matiere m

            INNER JOIN unite_enseignements ue ON ue.id = m.unite_enseignements_id
            INNER JOIN mention men ON men.id = ue.mention_id
            LEFT JOIN parcours parc ON parc.id = ue.parcours_id
            INNER JOIN niveau niv ON niv.id = ue.niveau_id
            INNER JOIN semestre sem ON sem.id = ue.semestre_id
            
            WHERE m.enseignant_id = :enseignantId
            GROUP BY m.id
            ORDER BY mentionNom ASC, niveauLibelle ASC, parcoursNom ASC, semestre ASC, ueLibelle ASC, matiereNom ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = [];
        foreach($statement->fetchAll() as $item) {
            $mentionItem = [];
            if(!array_key_exists($item['mentionNom'], $result)) {
                $result[$item['mentionNom']] = [];
                $result[$item['mentionNom']]['parcours'] = []; 
            }
            if($item['parcoursNom'] && !array_key_exists($item['parcoursNom'], $result[$item['mentionNom']]['parcours'])){
                $result[$item['mentionNom']]['parcours'][$item['parcoursNom']] = [];
            }
            $mentionItem['parcoursNom'] = $item['parcoursNom'];
            $mentionItem['niveauLibelle'] = $item['niveauLibelle'];
            $mentionItem['matiereId'] = $item['matiereId'];
            $mentionItem['matiereNom'] = $item['matiereNom'];
            $mentionItem['ueLibelle'] = $item['ueLibelle'];
            $mentionItem['semestre'] = $item['semestre'];
            if($item['parcoursNom'])
                array_push($result[$item['mentionNom']]['parcours'][$item['parcoursNom']], $mentionItem);
            else
                array_push($result[$item['mentionNom']], $mentionItem);
        }
        
        return $result;
    }

    /**
     * Get Enseignant matiere 
     * @param $mentionId
     * return matiere[]
     */
    public function getByMention(int $mentionId) {
        $sql = "
            select e.id as enseignant_id, CONCAT(`e`.`first_name` , ' ', `e`.`last_name`) as enseignant_name, em.mention_id, em.niveau_id, mat.id, mat.nom as matiereLibelle, mat.code as matiereCode, niv.libelle as niveauLibelle, ue.libelle as ueNom, sem.libelle as semestreNom, p.nom as parcoursNom from enseignant e 
            inner join enseignant_mention em on em.enseignant_id = e.id
            inner join matiere mat on mat.enseignant_id = e.id
            inner join unite_enseignements ue on ue.id = mat.unite_enseignements_id
            left join parcours p on p.id = ue.parcours_id
            inner join niveau niv on niv.id = ue.niveau_id
            inner join semestre sem on sem.id = ue.semestre_id

            WHERE em.mention_id = :mentionId
            AND ue.mention_id = :mentionId
            GROUP BY mat.id
            ORDER BY niveauLibelle ASC, p.nom ASC, enseignant_name ASC, matiereCode ASC, matiereLibelle ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = [];
        foreach($statement->fetchAll() as $item) {
            $niveauItem = [];
            if(!array_key_exists($item['niveauLibelle'], $result)) {
                $result[$item['niveauLibelle']] = [];
            }
            $niveauItem['enseignantName'] = $item['enseignant_name'];
            $niveauItem['matiereNom'] = $item['matiereLibelle'];
            $niveauItem['matiereCode'] = $item['matiereCode'];
            $niveauItem['niveau_id'] = $item['niveau_id'];
            $niveauItem['ueNom'] = $item['ueNom'];
            $niveauItem['semestreNom'] = $item['semestreNom'];
            $niveauItem['parcoursNom'] = $item['parcoursNom'];
            array_push($result[$item['niveauLibelle']], $niveauItem);
        }

        return $result;
    }

    /**
     * Get All mention UE matiere 
     * @param $mentionId
     * return matiere[]
     */
    public function getAllByMention(int $mentionId) {
        $sql = "
            SELECT mat.*, ue.mention_id, ue.niveau_id from matiere mat 
            INNER JOIN unite_enseignements ue on ue.id = mat.unite_enseignements_id

            WHERE ue.mention_id = :mentionId
            ORDER BY mat.nom ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }


    /**
     * Set All enseignant matiere to null
     * @param $enseignant
     * return void
     */
    public function setByEnseignantToNull(int $enseignantId) {
        $sql = "
            UPDATE matiere mat 
            SET enseignant_id = ''

            WHERE enseignant_id = :enseignantId
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('enseignantId', $enseignantId, \PDO::PARAM_INT);
        $statement->executeQuery();
        // $qb = $this->getEm()->createQueryBuilder("App\Entity\Matiere");
        // $qb->update()
        //     ->set('enseignant_id', "100000")
        //     ->where('enseignant_id = :enseignantId')
        //     ->setParameter('enseignantId', $enseignantId)
        //     ->getQuery()
        //     ->execute();

        return true;
    }


    /**
     * Get all by 
     * @param $options=[mention, niveau, parcours, semestre]
     * return matiere[]
     */
    public function getAllByFilters($options) {
        $queryFilter = $options['parcours'] ? "AND ue.parcours_id = :pParcours" : "";
        $queryFilter .= $options['semestre'] ? " AND ue.semestre_id = :pSemestre" : "";
        $sql = "
            SELECT mat.*, ue.libelle as ueLibelle, sem.libelle as semestre from matiere mat 
            INNER JOIN unite_enseignements ue on ue.id = mat.unite_enseignements_id
            INNER JOIN semestre sem on sem.id = ue.semestre_id

            WHERE ue.mention_id = :pMention
            AND ue.niveau_id = :pNiveau
            $queryFilter
            -- AND mat.enseignant_id IS NOT NULL
            GROUP BY mat.id
            ORDER BY sem.libelle ASC, mat.nom ASC
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pMention', $options['mention'], \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $options['niveau'], \PDO::PARAM_INT);
        if($options['parcours']) $statement->bindParam('pParcours', $options['parcours'], \PDO::PARAM_INT);
        if($options['semestre']) $statement->bindParam('pSemestre', $options['semestre'], \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->fetchAll();
        return $result;
    }

}