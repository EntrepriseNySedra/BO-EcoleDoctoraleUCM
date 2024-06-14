<?php

namespace App\Repository;

use App\Entity\CalendrierPaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierPaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierPaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierPaiement[]    findAll()
 * @method CalendrierPaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierPaiement::class);
    }

    // /**
    //  * @return CalendrierPaiement[] Returns an array of CalendrierPaiement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    
    public function findDefault(): ?CalendrierPaiement
    {
        $today=date("Y-m-d");
        $qb= $this->createQueryBuilder('c')
            ->Where('c.date_debut <= :val')
            ->andWhere('c.date_fin >= :val')
            ->setParameter('val', $today)
            ->getQuery();
        $result = $qb->getOneOrNullResult();
        return $result ? $result : $this->findOneBy([], []);
    }
    
}
