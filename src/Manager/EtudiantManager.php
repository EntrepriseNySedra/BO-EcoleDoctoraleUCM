<?php

namespace App\Manager;

use App\Entity\AnneeUniversitaire;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;

/**
 * Class EtudiantManager
 *
 * @package App\Manager
 */
class EtudiantManager extends BaseManager
{
    /**
     * Get All mention UE matiere
     *
     * @param int $mentionId
     * return matiere[]
     * @param int $niveauId
     * @param int $_parcoursId
     *
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllByMentionAndNiveau(int $mentionId,int $niveauId, int $_parcoursId) {
        $sqlPacoursFilter = $_parcoursId ? "AND et.parcours_id = :parcoursId" : "";
        $sql = "
            SELECT et.id FROM etudiant et 
            WHERE et.mention_id = :mentionId
            AND et.niveau_id = :niveauId
            $sqlPacoursFilter
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('mentionId', $mentionId, \PDO::PARAM_INT);
        $statement->bindParam('niveauId', $niveauId, \PDO::PARAM_INT);
        if($_parcoursId)
            $statement->bindParam('parcoursId', $_parcoursId, \PDO::PARAM_INT);
        $statement->executeQuery();

        $result = $statement->rowCount();
        return $result;
    }

    /**
     * @param \App\Entity\Mention            $mention
     * @param \App\Entity\AnneeUniversitaire $collegeYear
     *
     * @return mixed
     */
    public function getByMentionAndCollegeYear(Mention $mention, AnneeUniversitaire $collegeYear)
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
                   ->innerJoin(Inscription::class, 'i')
                   ->where('i.mention = :mention')
                   ->setParameter('mention', $mention)
                   ->andWhere('i.etudiant = e.id')
                   ->andWhere('i.anneeUniversitaire = :collegeYear')
                   ->setParameter('collegeYear', $collegeYear)
                   ->groupBy('e.id')
                   ->orderBy('e.firstName', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \App\Entity\Mention            $mention
     * @param \App\Entity\AnneeUniversitaire $collegeYear
     *
     * @return mixed
     */
    public function getAllOrderByMention()
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
                   ->innerJoin(Mention::class, 'm')
                   ->orderBy('m.nom', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \App\Entity\Mention            $mention
     * @param \App\Entity\AnneeUniversitaire $collegeYear
     * @param \App\Entity\Niveau             $niveau
     * @param \App\Entity\Parcours|null      $parcours
     *
     * @return mixed
     */
    public function getByInscriptionAndCollegeYear(
        Mention $mention,
        AnneeUniversitaire $collegeYear,
        Niveau $niveau,
        Parcours $parcours = null
    ) {
        $qb = $this->getRepository()->createQueryBuilder('e')
                   ->innerJoin(Inscription::class, 'i')
                   ->where('i.mention = :mention')
                   ->setParameter('mention', $mention)
                   ->andWhere('i.etudiant = e.id')
                   ->andWhere('i.anneeUniversitaire = :collegeYear')
                   ->setParameter('collegeYear', $collegeYear)
        ;

        $qb
            ->andWhere('i.Niveau = :niveau')
            ->setParameter('niveau', $niveau)
        ;

        if ($parcours) {
            $qb
                ->andWhere('i.Parcours = :parcours')
                ->setParameter('parcours', $parcours)
            ;
        }

        $qb
            ->groupBy('e.id')
            ->orderBy('e.firstName', 'ASC')
        ;

        $sql = $this->getFullSQL($qb->getQuery());

        return $qb->getQuery()->getResult();
    }



    /**
     * @param \App\Entity\Mention            $mention
     * @param \App\Entity\AnneeUniversitaire $collegeYear
     * @param \App\Entity\Niveau             $niveau
     * @param \App\Entity\Parcours|null      $parcours
     *
     * @return mixed
     */
    public function getActive(
        $mention,
        $niveau,
        Parcours $parcours = null
    ) {
        $statusActive = Etudiant::STATUS_ACTIVE;
        $qb = $this->getRepository()->createQueryBuilder('e')
                   ->where('e.mention = :mention')
                   ->setParameter('mention', $mention)
                   ->andWhere('e.status = :status')
                   ->setParameter('status', $statusActive)
        ;
        $qb
            ->andWhere('e.niveau = :niveau')
            ->setParameter('niveau', $niveau)
        ;
        if ($parcours) {
            $qb
                ->andWhere('e.parcours = :parcours')
                ->setParameter('parcours', $parcours)
            ;
        }
        $qb
            ->groupBy('e.id')
            ->orderBy('e.immatricule', 'ASC')
        ;
        $sql = $this->getFullSQL($qb->getQuery());

        return $qb->getQuery()->getResult();
    }

}