<?php

namespace App\Manager;

use App\Entity\AnneeUniversitaire;

/**
 * Class ExtraNotesManager
 *
 * @package App\Manager
 */
class ExtraNotesManager extends BaseManager
{
    /**
     * @param array                          $students
     * @param \App\Entity\AnneeUniversitaire $collegeYear
     *
     * @return mixed
     */
    public function getByStudentsAndCollegeYear(array $students, AnneeUniversitaire $collegeYear)
    {
        $qb = $this->getRepository()->createQueryBuilder('n')
                   ->where('n.etudiant IN (:students)')
                   ->setParameter('students', $students)
                   ->andWhere('n.anneeUniversitaire = :collegeYear')
                   ->setParameter('collegeYear', $collegeYear)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int    $studentId
     * @param int    $collegeYearId
     * @param string $type
     *
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isUnique(int $studentId, int $collegeYearId, string $type)
    {
        $sql = "
                SELECT COUNT(id) FROM `extra_note` en
                WHERE `en`.etudiant_id = :studentId
                AND `en`.`annee_universitaire_id` = :collegeYearId
                AND `en`.type = :type
        ";

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindValue('studentId', $studentId);
        $statement->bindValue('collegeYearId', $collegeYearId);
        $statement->bindValue('type', $type);
        $statement->executeQuery();

        return ((int) $statement->fetchColumn() == 0);

    }
}