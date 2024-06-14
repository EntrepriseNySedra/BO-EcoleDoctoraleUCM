<?php

namespace App\Services;

use App\Entity\EmploiDuTemps;
use App\Entity\Prestation;

/**
 * Description of WorkflowStatutService.php.
 *
 * @package App\Services
 */
class WorkflowStatutService
{

    const STATUS_CREATED                = 1;
    const STATUS_ASSIST_VALIDATED       = 2;
    const STATUS_CM_VALIDATED           = 3;
    const STATUS_COMPTA_VALIDATED       = 4;
    const STATUS_RF_VALIDATED           = 5;
    const STATUS_SG_VALIDATED           = 6;
    const STATUS_RECTEUR_VALIDATED      = 7;

    private $_edtNextRoles = [
        self::STATUS_CREATED                => self::STATUS_ASSIST_VALIDATED,
        self::STATUS_ASSIST_VALIDATED       => self::STATUS_CM_VALIDATED,
        self::STATUS_CM_VALIDATED           => self::STATUS_COMPTA_VALIDATED,
        self::STATUS_COMPTA_VALIDATED       => self::STATUS_RF_VALIDATED,
        self::STATUS_RF_VALIDATED           => self::STATUS_SG_VALIDATED,
        self::STATUS_SG_VALIDATED           => self::STATUS_RECTEUR_VALIDATED,
    ];

    /**
     * @param user roles $_userRoles
     * @return $statut
     */
    public function getStatutForListByProfil($_userRoles=array())
    {
        $statut = '';
        if(in_array('ROLE_ASSISTANT', $_userRoles)) {
            $statut = self::STATUS_CREATED;
        }
        if(in_array('ROLE_CHEFMENTION', $_userRoles)) {
            $statut = self::STATUS_ASSIST_VALIDATED;
        }
        if(in_array('ROLE_COMPTABLE', $_userRoles)) {
            $statut = self::STATUS_CM_VALIDATED;
        }
        if(in_array('ROLE_RF', $_userRoles)) {
            $statut = self::STATUS_COMPTA_VALIDATED;
        }
        if(in_array('ROLE_SG', $_userRoles)) {
            $statut = self::STATUS_RF_VALIDATED;
        }
        if(in_array('ROLE_RECTEUR', $_userRoles)) {
            $statut = self::STATUS_SG_VALIDATED;
        }
        
        return $statut;
    }

    /**
     * @param current resource statut $_currentStatut
     * @return $nextStatut
     */
    public function getResourceNextStatut($_currentStatut="")
    {
        return $this->_edtNextRoles[$_currentStatut];
    }

    /**
     * @param current resource statut $_currentStatut
     * @return $previousStatut
     */
    public function getEdtPreviousStatut($_currentStatut="")
    {
        $_edtPreviousRoles = array_flip($this->_edtNextRoles);
        return isset($_edtPreviousRoles[$_currentStatut]) ? $_edtPreviousRoles[$_currentStatut] : $_currentStatut ;
    }
}