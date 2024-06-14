<?php

namespace App\Manager;

use App\Entity\Profil;
use App\Entity\Roles;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class ProfilManager
 *
 * @package App\Manager
 * @author Joelio
 */
class ProfilManager extends BaseManager
{

    /**
     * @param string            $name
     * @param \App\Entity\Roles $role
     *
     * @return \App\Entity\Profil
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $name, Roles $role)
    {
        /** @var \App\Entity\Profil $profil */
        $profil = $this->createObject();
        $profil->setName($name);
        $profil->setStatus(Profil::STATUS_ENABLED);
        $profil->addRole($role);

        $this->save($profil);

        return $profil;
    }

    /**
     * @param string $code
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByRoleCode(string $code)
    {
        $sql = "
            SELECT p.* FROM `profil` p
            INNER JOIN `profil_has_roles` phr ON `p`.`id` = `phr`.`profil_id`
            INNER JOIN `roles` r ON `r`.`id` = `phr`.`roles_id`
            WHERE `p`.`status` = :status
            AND `r`.`code` = :code
        ";

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'name', 'name');

        $query = $this->em->createNativeQuery($sql, $rsm)
                          ->setParameter('status', Profil::STATUS_ENABLED)
                          ->setParameter('code', $code)
        ;

        return $query->getOneOrNullResult();
    }
}