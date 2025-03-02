<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    // /**
    //  * @return Evenement[] Returns an array of Evenement objects
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
    public function findOneBySomeField($value): ?Evenement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */



    /**
     * @return Evenement[]
     * Get all available event i.e filter by today date
     */
    public function findAllAvailable(): array
    {
        // $entityManager = $this->getEntityManager();

        // $query = $entityManager->createQuery(
        //     'SELECT evt
        //     FROM App\Entity\Evenement evt
        //     WHERE p.price >= curdate()
        //     ORDER BY evt.title ASC'
        // );

        
        // return $query->getResult();




        return $this->createQueryBuilder('evt')
            ->andWhere('evt.date >= NOW()')
            ->orderBy('evt.title', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;



    }


}
