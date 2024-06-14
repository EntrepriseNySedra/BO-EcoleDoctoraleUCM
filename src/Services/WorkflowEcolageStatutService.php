<?php

namespace App\Services;

/**
 * Description of WorkflowEcolageStatutService.php.
 *
 * @package App\Services
 */
class WorkflowEcolageStatutService extends WorkflowStatutService
{

    const STATUS_CREATED                = 1;
    const STATUS_SRS_VALIDATED          = 2;
    const STATUS_COMPTA_VALIDATED       = 3;
    const STATUS_RAF_VALIDATED          = 4;
    const STATUS_ARCHIVED               = 5;
    

    private $_edtNextRoles = [
        self::STATUS_CREATED                => self::STATUS_SRS_VALIDATED,
        self::STATUS_SRS_VALIDATED       => self::STATUS_COMPTA_VALIDATED,
        self::STATUS_COMPTA_VALIDATED       => self::STATUS_RAF_VALIDATED
    ];

    /**
     * @param user roles $_userRoles
     * @return $statut
     */
    public function getStatutForListByProfil($_userRoles=array())
    {
        $statut = '';
        if( count(array_intersect(['ROLE_ETUDIANT', 'ROLE_SCOLARITE'], $_userRoles)) > 0 ) {
            $statut = self::STATUS_CREATED;
        }
        if(in_array('ROLE_SCOLARITE', $_userRoles)) {
            $statut = self::STATUS_CREATED;
        }
        if(in_array('ROLE_COMPTABLE', $_userRoles)) {
            $statut = self::STATUS_SRS_VALIDATED;
        }
        if(in_array('ROLE_RF', $_userRoles)) {
            $statut = self::STATUS_COMPTA_VALIDATED;
        }
        
        return $statut;
    }
}