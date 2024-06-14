<?php

namespace App\Manager;

use App\Entity\Profil;

/**
 * Description of UserManager.php.
 *
 * @package App\Manager
 */
class UserManager extends BaseManager
{

    /**
     * @param string $roleCode
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByRoleCode(string $roleCode)
    {
        $sql = "
            SELECT `u`.* FROM `roles` r
            INNER JOIN `profil_has_roles` phr ON `phr`.`roles_id` = `r`.`id`
            INNER JOIN `profil` p ON `p`.`id` = `phr`.`profil_id`
            INNER JOIN `user` u ON `u`.`profil_id` = `p`.`id`
            WHERE `r`.`code` = :roleCode
            AND `p`.`status` = :status
        ";

        $status = Profil::STATUS_ENABLED;

        $connection = $this->em->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->bindParam('roleCode', $roleCode, \PDO::PARAM_STR);
        $statement->bindParam('status', $status, \PDO::PARAM_STR);
        $statement->executeQuery();

        return $statement->fetchAll();
    }
}