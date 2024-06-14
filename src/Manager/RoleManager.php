<?php

namespace App\Manager;

/**
 * Class RoleManager
 *
 * @package App\Manager
 * @author Joelio
 */
class RoleManager extends BaseManager
{

    /**
     * @param string $name
     * @param string $code
     *
     * @return \App\Entity\Roles
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $name, string $code)
    {
        /** @var \App\Entity\Roles $role */
        $role = $this->createObject();
        $role->setName($name);
        $role->setCode($code);

        $this->save($role);

        return $role;
    }
}