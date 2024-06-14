<?php

namespace App\Repository;

use App\Entity\Etudiant;
use App\Entity\FraisScolarite;
use App\Entity\Inscription;
use App\Entity\Mention;
use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Etudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiant[]    findAll()
 * @method Etudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiant::class);
    }

    // /**
    //  * @return Etudiant[] Returns an array of Etudiant objects
    //  */
    
    public function getPerClass($_mentionId, $_niveauId, $_parcoursId, $_anneUnivId)
    {
        $em = $this->getEntityManager();
        $sqlPacoursFilter = $_parcoursId ? "AND e.parcours_id = :pParcours" : "";
        $sql = "
            SELECT e.* 
            FROM  etudiant e
            INNER JOIN inscription i ON i.etudiant_id = e.id
            WHERE e.mention_id = :pMention
            AND e.niveau_id = :pNiveau
            $sqlPacoursFilter
            AND i.annee_universitaire_id = :pAnneeUniversitaire

            ORDER BY e.last_name ASC, e.first_name ASC
        ";

        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pMention', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $_niveauId, \PDO::PARAM_INT);
        if($_parcoursId) $statement->bindParam('pParcours', $_parcoursId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeUniversitaire', $_anneUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();
        
        return $statement->fetchAll();
    }  

    // /**
    //  * @return Etudiant[] Returns an array of Etudiant objects
    //  */
    
    public function getInscritPerClass($_mentionId, $_niveauId, $_parcoursId, $_anneUnivId)
    {
        $inscritStat = Inscription::STATUS_VALIDATED;
        $em = $this->getEntityManager();
        $sqlPacoursFilter = $_parcoursId ? "AND e.parcours_id = :pParcours" : "";
        $sql = "
            SELECT DISTINCT e.* 
            FROM etudiant e
            INNER JOIN inscription i ON i.etudiant_id = e.id
            WHERE e.mention_id = :pMention
            AND e.niveau_id = :pNiveau
            $sqlPacoursFilter
            AND i.status = '$inscritStat'
            AND i.annee_universitaire_id = :pAnneeUniversitaire
            ORDER BY e.last_name ASC, e.first_name ASC;
        ";

        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pMention', $_mentionId, \PDO::PARAM_INT);
        $statement->bindParam('pNiveau', $_niveauId, \PDO::PARAM_INT);
        if($_parcoursId) $statement->bindParam('pParcours', $_parcoursId, \PDO::PARAM_INT);
        $statement->bindParam('pAnneeUniversitaire', $_anneUnivId, \PDO::PARAM_INT);
        $statement->executeQuery();
        
        return $statement->fetchAll();
    }    

    /*
    public function findOneBySomeField($value): ?Etudiant
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findLastRegistered(Mention $mention)
    {
        // $sqlResult = $this->createQueryBuilder('e')
        //     //->addSelect('m') // to make Doctrine actually use the join
        //     ->innerJoin(User::class, 'u')
        //     // ->andWhere('e.mention = :mention')
        //     // ->setParameter('mention', $mention)
        //     ->andWhere('u.fromImport = :val')
        //     ->setParameter('val', 0)
        //     ->orderBy('e.createdAt', 'DESC')
        //     ->getQuery()
        //     ->getResult();
        //     if($sqlResult && count($sqlResult) > 0) return $sqlResult[0];
        //     return null;

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT e FROM App\Entity\etudiant e 
            INNER JOIN e.user u 
            WHERE u.fromImport = 0
            AND e.mention = :mention
            ORDER BY e.createdAt DESC'
        );
        $query->setParameter('mention', $mention);
        $sqlResult = $query->getResult();
        if($sqlResult && count($sqlResult) > 0) return $sqlResult[0];
            return null;
    }    
}
