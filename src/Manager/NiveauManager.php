<?php

namespace App\Manager;
use App\Entity\Niveau;
/**
 * Class NiveauManager
 *
 * @package App\Manager
 */
class NiveauManager extends BaseManager
{
    /**
     * GET concours L1, M1 ,L3 niveau
     * return niveau[]
     */
    public function getConcourable() {
        $query = sprintf("
            SELECT id, libelle FROM niveau
            WHERE code = '%s' OR code = '%s'OR code = '%s'
            ORDER BY libelle ASC
        ", Niveau::L1_CODE, Niveau::M1_CODE, Niveau::L3_CODE);

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}