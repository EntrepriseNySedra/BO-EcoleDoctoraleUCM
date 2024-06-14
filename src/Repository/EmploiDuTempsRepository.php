<?php

namespace App\Repository;

use App\Entity\EmploiDuTemps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmploiDuTemps|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmploiDuTemps|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmploiDuTemps[]    findAll()
 * @method EmploiDuTemps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmploiDuTempsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmploiDuTemps::class);
    }

    // /**
    //  * @return EmploiDuTemps[] Returns an array of EmploiDuTemps objects
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
    public function findOneBySomeField($value): ?EmploiDuTemps
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function findEdt($begin,$end) 
    {
        $connexion = $this->getEntityManager()->getConnection();
        $stmt = $connexion->query(
            "SELECT 
                    edt.start_date,
                    edt.end_date,
                    mtr.nom,
                    mtr.credit,
                    sls.libelle,
                    sls.status
                FROM emploi_du_temps as edt
                INNER JOIN matiere as mtr
                    ON edt.matiere_id = mtr.id
                INNER JOIN salles as sls
                    ON edt.salles_id = sls.id
                WHERE DATE_FORMAT(edt.start_date, '%Y-%m-%d') >= '".$begin."' 
		AND DATE_FORMAT(edt.end_date, '%Y-%m-%d') <= '".$end."' 
                ORDER BY edt.start_date
            ");

        $stmt->execute();

        $edt = $stmt->fetchAll();

        return $edt;
    }
}
