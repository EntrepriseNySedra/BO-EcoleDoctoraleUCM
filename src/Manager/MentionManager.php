<?php

namespace App\Manager;

use App\Entity\AnneeUniversitaire;
use App\Entity\Inscription;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class MentionManager
 *
 * @package App\Manager
 */
class MentionManager extends BaseManager
{
    public function getByInscriptionAndCollegeYear(
        AnneeUniversitaire $anneeUniversitaire,
        int $mentionId = 0
    ) {
        $qb = $this->getRepository()->createQueryBuilder('m')
                   ->innerJoin(Inscription::class, 'i')
                   ->where('i.anneeUniversitaire = :anneeUniversitaire')
                   ->setParameter('anneeUniversitaire', $anneeUniversitaire)
        ;
        if ($mentionId) {
            $qb
                ->andWhere('i.mention = m.id')
                ->andWhere('m.id = :mention')
                ->setParameter('mention', $mentionId)
            ;
        }
        $qb->orderBy('m.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get active concours mention
     * @return mention[]
     */
    public function findConcourable()
    {
        $sql = "
            SELECT DISTINCT m.id, m.nom FROM mention m 
            INNER JOIN concours c ON c.mention_id = m.id
            WHERE DATE_FORMAT(c.start_date, \"%Y-%m-%d\") < curdate() AND DATE_FORMAT(c.end_date, \"%Y-%m-%d\") > curdate()
        ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}