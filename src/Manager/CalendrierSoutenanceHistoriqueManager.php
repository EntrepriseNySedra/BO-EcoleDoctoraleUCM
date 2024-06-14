<?php

namespace App\Manager;

use App\Entity\CalendrierSoutenance;
use App\Entity\User;

/**
 * Class CalendrierSoutenanceHistoriqueManager
 *
 * @package App\Manager
 */
class CalendrierSoutenanceHistoriqueManager extends BaseManager
{
    public function getInstance(CalendrierSoutenance $calendrierSoutenance, User $user, string $status)
    {
        $object = $this->loadOneBy(
            [
                'calendrierSoutenance' => $calendrierSoutenance,
                'user'                 => $user,
                'status'               => $status
            ]
        );

        if (!$object) {
            $object = $this->createObject();
        }

        return $object;
    }
}