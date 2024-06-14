<?php

namespace App\Repository;

use App\Entity\FraisScolarite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FraisScolarite|null find($id, $lockMode = null, $lockVersion = null)
 * @method FraisScolarite|null findOneBy(array $criteria, array $orderBy = null)
 * @method FraisScolarite[]    findAll()
 * @method FraisScolarite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FraisScolariteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FraisScolarite::class);
    }

    // /**
    //  * @return FraisScolarite[] Returns an array of FraisScolarite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FraisScolarite
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * Get last paiement ecolage
     * @param semestre
     */
    public function getEtudiantPaiement($etudiant, $anneUniv) {
        $qb = $this->createQueryBuilder('fs')
                   ->where('fs.etudiant = :etudiant')
                   ->setParameter('etudiant', $etudiant)
                   ->andWhere('fs.annee_universitaire = :anneUniv')
                   ->setParameter('anneUniv', $anneUniv)
        ;
        $qb->orderBy('fs.updated_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get last paiement ecolage
     * @param semestre
     */
    public function getEtudiantLastPaiement($etudiant, $semestre, $anneUniv, $current=null) {
        $qb = $this->createQueryBuilder('fs')
                   ->where('fs.etudiant = :etudiant')
                   ->setParameter('etudiant', $etudiant);
                   if($current) {
                        $qb->andWhere('fs.id != :id')
                        ->setParameter('id', $current->getId());
                   }                   
                   $qb->andWhere('fs.semestre = :semestre')
                   ->setParameter('semestre', $semestre)
                   ->andWhere('fs.annee_universitaire = :anneUniv')
                   ->setParameter('anneUniv', $anneUniv)
        ;
        $qb->orderBy('fs.created_at', 'DESC');

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }


    /**
     * Get reste paiement ecolage
     * @param semestre
     */
    public function getEtudiantRestePaiement($etudiant, $semestre, $anneUniv, $current=null) {
        $qb = $this->createQueryBuilder('fs')
                   ->where('fs.etudiant = :etudiant')
                   ->setParameter('etudiant', $etudiant);
                   if($current) {
                        $qb->andWhere('fs.id != :id')
                        ->setParameter('id', $current->getId());
                   }                   
                   $qb->andWhere('fs.semestre = :semestre')
                   ->setParameter('semestre', $semestre)
                   ->andWhere('fs.annee_universitaire = :anneUniv')
                   ->setParameter('anneUniv', $anneUniv)
        ;
        $qb->orderBy('fs.date_paiement', 'DESC');

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function getPerEtudiantGoupBySem($etudiantIds=array(), Int $anneUnivId)
    {
        $ids = "'".join("','", $etudiantIds)."'";
        $conn = $this->getEntityManager()->getConnection();
        $sql="
            SELECT fs.id as fsId, SUM(montant) as montant, fs.semestre_id as semestre, fs.mention_id as mention, fs.niveau_id as niveau, fs.parcours_id as parcours, et.id as etudiantId, et.first_name,fs. annee_universitaire_id FROM etudiant et
            LEFT JOIN frais_scolarite fs ON et.id = fs.etudiant_id
            WHERE et.id IN ($ids)
            AND fs.annee_universitaire_id = :pAnneeUniv
            GROUP BY et.id, fs.semestre_id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam('pAnneeUniv', $anneUnivId, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAll();
    }
}
