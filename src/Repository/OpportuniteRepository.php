<?php

namespace App\Repository;

use App\Entity\Opportunite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Opportunite>
 *
 * @method Opportunite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Opportunite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Opportunite[]    findAll()
 * @method Opportunite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OpportuniteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Opportunite::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Opportunite $entity, bool $flush = true): void
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
    public function remove(Opportunite $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getAllOpportunite()
    {
        $qB = $this->createQueryBuilder('a')
        ->select('a')
        ->where('a.active = :active')
        ->setParameter('active', 1)
        ->orderBy('a.createdAt', 'DESC')
        ->orderBy('a.updatedAt', 'DESC');

    return $qB->getQuery()->getResult();
    }

    public function getLastOpportunite()
    {

        $qB = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.active = :active')
            ->setParameter('active', 1)
            ->orderBy('a.createdAt', 'DESC')
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults(10);

        return $qB->getQuery()->getResult();
    }


  
    // /**
    //  * @return Opportunite[] Returns an array of Opportunite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Opportunite
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
