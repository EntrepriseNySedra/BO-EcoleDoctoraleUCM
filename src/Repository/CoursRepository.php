<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * @return matiere[] : an array of matieres objects
     * @param of filter fields
     */
    
    public function findAllByUserLevel($niveau, $mention)
    {
        $entityManager = $this->getEntityManager();
        // $query = $entityManager->createQuery(
        //     'SELECT m FROM App\Entity\Matiere m 
        //     INNER JOIN m.uniteEnseignements ue 
        //     INNER JOIN m.cours c
        //     LEFT JOIN c.coursMedia
        //     ORDER BY ue.libelle ASC'
        // );
        // $query->andWhere();
        // $query->setParameter('niv', $niveau);
        // $query->setParameter('ment', $mention);
        // $reqResult = $query->getResult();

        // return $query->getResult();



        $query = $this->createQueryBuilder('m')
        ->addSelect('m') // to make Doctrine actually use the join
        ->join('m.uniteEnseignements', 'ue')
        ->join('m.cours', 'c')
        ->leftJoin('c.coursMedia', 'cm')
        // ->where('r.foo = :parameter')
        // ->setParameter('parameter', $parameter)
        ->getQuery();

        return $query->getResult();


    }
    

    /*
    public function findOneBySomeField($value): ?Cours
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
