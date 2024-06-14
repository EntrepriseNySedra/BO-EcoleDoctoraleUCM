<?php

namespace App\Repository;

use App\Entity\Matiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Matiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Matiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Matiere[]    findAll()
 * @method Matiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

    // /**
    //  * @return Matiere[] Returns an array of Matiere objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Matiere
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function setByEnseignantToNull($value)
    {
        return $this->createQueryBuilder('m')
            ->update()
            ->where('m.enseignant = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->execute()
        ;
    }

    public function getByUeEnseignantNotNull($value)
    {
        return $this->createQueryBuilder('m')
            ->where('m.enseignant = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return matiere[] : an array of matieres objects
     * @param of filter fields
     */
    
    public function findAllByUserLevel($niveau, $mention, $anneUniv, $parcours=null)
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

        $query1 = $this->createQueryBuilder('m')
        // ->addSelect('ni') // to make Doctrine actually use the join
        ->join('m.uniteEnseignements', 'ue')
        ->leftJoin('ue.parcours', 'p')
        ->join('m.cours', 'c')
        ->leftJoin('c.coursMedia', 'cm')
        ->where('ue.mention = :mention')
        ->andWhere('ue.niveau = :niveau')
        ->andWhere('c.anneeUniversitaire = :anneeUniv');
        $query2 = $query1;
        if($parcours) {
            $query2 = $query1->andWhere('ue.parcours = :parcours');
            $query2->setParameter('parcours', $parcours);
        }

        $query3 = $query2->setParameter('mention', $mention)
        ->setParameter('niveau', $niveau)        
        ->setParameter('anneeUniv', $anneUniv)        
        ->getQuery();

        return $query3->getResult();


    }
}
