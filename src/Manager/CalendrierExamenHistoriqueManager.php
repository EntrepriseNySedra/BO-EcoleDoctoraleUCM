<?php

namespace App\Manager;

use App\Entity\CalendrierExamen;
use App\Entity\User;

/**
 * Class CalendrierExamenHistoriqueManager
 *
 * @package App\Manager
 */
class CalendrierExamenHistoriqueManager extends BaseManager
{
    public function getInstance(CalendrierExamen $CalendrierExamen, User $user, string $status)
    {
        $object = $this->loadOneBy(
            [
                'CalendrierExamen' => $CalendrierExamen,
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