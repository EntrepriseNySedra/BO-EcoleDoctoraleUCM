<?php

namespace App\Repository;

use App\Entity\EtudiantDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EtudiantDocument>
 *
 * @method EtudiantDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtudiantDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtudiantDocument[]    findAll()
 * @method EtudiantDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtudiantDocument::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EtudiantDocument $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(EtudiantDocument $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getPerEtudiant($_coursId)
    {
        $em = $this->getEntityManager();
        $sql = "
            SELECT ed.* 
            FROM  etudiant_document ed
            INNER JOIN cours c ON c.id = ed.cours_id
            WHERE ed.cours_id = :pCours
        ";

        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindParam('pCours', $_coursId, \PDO::PARAM_INT);
        
        
        $statement->executeQuery();
        
        return $statement->fetchAll();
    }  

    // /**
    //  * @return EtudiantDocument[] Returns an array of EtudiantDocument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtudiantDocument
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
