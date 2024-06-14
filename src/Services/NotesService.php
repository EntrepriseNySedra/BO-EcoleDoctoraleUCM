<?php

namespace App\Services;

use App\Entity\Niveau;
use App\Entity\UniteEnseignements;

/**
 * Description of NotesService.php.
 *
 * @package App\Services
 * @author Joelio
 */
class NotesService
{
    /**
     * @param array $notes
     *
     * @return array
     */
    public function getNotesUE(array $notes) : array
    {
        $notesUE = [];

        foreach ($notes as $note) {
            $notesUE[$note['unite_enseignements_id']][$note['id']] = $note;
        }

        return $notesUE;
    }

    /**
     * @param array $notesUE
     *
     * @return array
     */
    public function getMoyennesUE(array $notesUE) : array
    {
        $moyennesUE = [];

        foreach ($notesUE as $ueId => $noteUE) {
            $totalNote = 0;
            foreach ($noteUE as $note) {
                $totalNote += (float) $note['note'];
            }
            $moyennesUE[$ueId] = round($totalNote / count($notesUE[$ueId]), 2, PHP_ROUND_HALF_DOWN);
        }

        return $moyennesUE;
    }

    /**
     * @param array $moyennesUE
     *
     * @return float|int
     */
    public function getMoyenneG(array $moyennesUE)
    {
        $moyenneG = 0;
        foreach ($moyennesUE as $ueId => $moyenne) {
            $moyenneG += $moyenne;
        }

        return (count($moyennesUE) > 0) ? round($moyenneG / count($moyennesUE), 2, PHP_ROUND_HALF_DOWN) : 0;
    }




    /**
     * @param $notes[]
     *
     * @return mixed[]
     */
    public function getNotePerTypeUE(array $notes)
    {
        
        $resultUe = [];
        foreach($notes as $note) {
            if(!array_key_exists($note['ueType'], $resultUe)){
                $resultUe[$note['ueType']] = [];    
                $sumNoteEC  = 0;
                $iNbrEC     = 1;
            } 
            $sumNoteEC += $note['note'];     
            $resultUe[$note['ueType']]['avgUe'] = $sumNoteEC / $iNbrEC;
            $resultUe[$note['ueType']]['EC'][] = $note;
            
            $iNbrEC++;
        }

        return $resultUe;
    }

    /**
     * @param $notesUE[]
     *
     * @return number
     */
    public function getMoyenneUE(array $notesUE)
    {
        $totalNoteUe = 
        (isset($notesUE['COMPLEMENTAIRE']['avgUe']) ? $notesUE['COMPLEMENTAIRE']['avgUe'] : 0) + 
        (isset($notesUE['FONDAMENTALE']['avgUe']) ? $notesUE['FONDAMENTALE']['avgUe'] : 0) + 
        (isset($notesUE['TRANSVERSALE']['avgUe']) ? $notesUE['TRANSVERSALE']['avgUe'] : 0);


        return count($notesUE) > 0 ? round($totalNoteUe / count($notesUE), 2, PHP_ROUND_HALF_DOWN) : 0;
    }

    /**
     * Get exam note result
     * @param $notes[], array notes of selected mention/niveau/parcours from current univ year
     * @return mixed data
     * */
    public static function getClassNoteSemestre(array $notes) {
        $results = [];
        // dump($notes);die;
        foreach( $notes as $note) {
            $etudiantId     = $note['etudiantId'];
            $noteValue      = $note['note'];
            $ueType         = $note['ueType'];
            $ueLibelle      = $note['ueLibelle'];
            $niveauValue    = $note['niveau'];
            $creditEc       = $note['matiereCredit'];
            $semestreId     = $note['semestreId'];
            $semestre       = $note['semestre'];
            
                

            if(!array_key_exists($etudiantId, $results)){

                $etudiantDataResult = [];
                $etudiantDataResult['firstName'] = $note['first_name'];
                $etudiantDataResult['lastName'] = $note['last_name'];
                $etudiantDataResult['semestre'] = [];
            } 

            if(!array_key_exists($semestreId, $etudiantDataResult['semestre'])){
                    
                $semDataResult = [];

                $iTotalNbrEcFondamentale    = 0;
                $iCreditUeFondamental       = 0;
                $iCrdFondamentaleObtenu     = 0;
                $iCrdFondamentaleFinal     = 0;
                $fTotalNoteFondamentale     = 0;
                $fMoyenneUeFondamentale     = 0;

                $fTotalNoteTransversale     = 0;
                $iTotalNbrEcTransversale    = 0;
                $iCrdTransversale           = 0;
                $iCrdTransversaleObtenu     = 0;
                $iCrdTransversaleFinal     = 0;
                $iCreditUeTransversale      = 0;
                $fMoyenneUeTransversale     = 0;

                $fTotalNoteComplementaire   = 0;
                $iTotalNbrEcComplementaire  = 0;
                $iCrdComplementaireObtenu   = 0;
                $iCrdComplementaireFinal   = 0;
                $iCrdComplementaire         = 0;
                $iCreditUeComplementaire    = 0;
                $fMoyenneUeComplementaire   = 0;                        

                $iTotalCredit       = 0;
                $iDivMoyenneGen     = 0;
                $iMoyenneGen = 0;
                $iTotalCreditObtenu = 0;
                $iTotalCreditFinal  = 0;

                $resultStatut       = '';

            }

            switch($ueType) {
                case UniteEnseignements::TYPE_FONDAMENRALE:
                    $fTotalNoteFondamentale  += $noteValue;
                    $iTotalNbrEcFondamentale += 1;
                    $iCreditUeFondamental    += $creditEc;
                    if($noteValue >= 10)
                        $iCrdFondamentaleObtenu += $creditEc;
                    $fMoyenneUeFondamentale = round($fTotalNoteFondamentale / $iTotalNbrEcFondamentale, 2, PHP_ROUND_HALF_DOWN);
                    break;
                case UniteEnseignements::TYPE_TRANSVERSALE:
                    $fTotalNoteTransversale  += $noteValue;
                    $iTotalNbrEcTransversale += 1;
                    $iCreditUeTransversale += $creditEc;
                    $fMoyenneUeTransversale = round($fTotalNoteTransversale / $iTotalNbrEcTransversale, 2, PHP_ROUND_HALF_DOWN);
                    if($noteValue >= 10)
                        $iCrdTransversaleObtenu += $creditEc;
                    elseif($noteValue < 10 && $fMoyenneUeTransversale >= 10)
                        $iCrdTransversaleFinal = $iCreditUeTransversale;
                    break;
                case UniteEnseignements::TYPE_COMPLEMENTAIRE:
                    $fTotalNoteComplementaire  += $noteValue;
                    $iTotalNbrEcComplementaire += 1;
                    $iCreditUeComplementaire   += $creditEc;
                    $fMoyenneUeComplementaire = round($fTotalNoteComplementaire / $iTotalNbrEcComplementaire, 2, PHP_ROUND_HALF_DOWN);
                    if($noteValue >= 10)
                            $iCrdComplementaireObtenu += $creditEc;
                    elseif($noteValue < 10 && $fMoyenneUeComplementaire >= 10) {
                        $iCrdComplementaireFinal = $iCreditUeComplementaire;
                    } 
                    break; 
            }

            $iDivMoyenneGen = ($iTotalNbrEcFondamentale > 0 ? 1 : 0) + ($iTotalNbrEcTransversale > 0 ? 1 : 0) + ($iTotalNbrEcComplementaire > 0 ? 1 : 0);
            $iMoyenneGen = round(($fMoyenneUeFondamentale  + $fMoyenneUeTransversale  + $fMoyenneUeComplementaire) / $iDivMoyenneGen, 2, PHP_ROUND_HALF_DOWN);

           if($noteValue < 10 &&  $iMoyenneGen >= 10) {
                $iCrdTransversaleFinal      = $iCreditUeTransversale;
                $iCrdComplementaireFinal    = $iCreditUeComplementaire;
            }

            $iTotalCreditObtenu = $iCrdFondamentaleObtenu + $iCrdTransversaleObtenu + $iCrdComplementaireObtenu;
            $iTotalCreditFinal  = $iCrdFondamentaleObtenu + $iCrdTransversaleFinal + $iCrdComplementaireFinal;
            $iTotalCredit += $creditEc;

            //Set result
            $resultStatut = '';
            if($iMoyenneGen < 10)
                $resultStatut = 'AJ';
            if($iMoyenneGen >= 10 && ($iTotalCredit - $iTotalCreditObtenu == 0))
                $resultStatut = 'ADM';
            if($iMoyenneGen >=10 && ($iTotalCredit - $iTotalCreditObtenu > 0) && ($iCreditUeFondamental - $iCrdFondamentaleObtenu == 0))
                $resultStatut = "ADC";
            if($iMoyenneGen >=10 && ($iTotalCredit - $iTotalCreditObtenu > 0) && ($iCreditUeFondamental - $iCrdFondamentaleObtenu > 0))
                $resultStatut = "AJAC";


            $semDataResult['fMoyenneUeFondamentale'] = $fMoyenneUeFondamentale;
            $semDataResult['fMoyenneUeTransversale'] = $fMoyenneUeTransversale;
            $semDataResult['fMoyenneUeComplementaire'] = $fMoyenneUeComplementaire;


            $semDataResult['moyenneGenerale'] = $iMoyenneGen;
            $semDataResult['iTotalCredit'] = $iTotalCredit;
            $semDataResult['iTotalCreditObtenu'] = $iTotalCreditObtenu;
            $semDataResult['iTotalCreditFinal'] = $iTotalCreditFinal;

            $semDataResult['iCreditUeFondamental'] = $iCreditUeFondamental;
            $semDataResult['iCrdFondamentaleObtenu'] = $iCrdFondamentaleObtenu;

            $semDataResult['iCreditUeTransversale'] = $iCreditUeTransversale;
            $semDataResult['iCrdTransversaleObtenu'] = $iCrdTransversaleObtenu;
            $semDataResult['iCrdTransversaleFinal'] = $iCrdTransversaleFinal;

            $semDataResult['iCreditUeComplementaire'] = $iCreditUeComplementaire;
            $semDataResult['iCrdComplementaireObtenu'] = $iCrdComplementaireObtenu;
            $semDataResult['iCrdComplementaireFinal'] = $iCrdComplementaireFinal;

            $semDataResult['resultStatut'] = $resultStatut;

            $etudiantDataResult['semestre'][$semestreId] = $semDataResult;

            $results[$etudiantId] = $etudiantDataResult ;

        }//end foreach

        return $results;
    }

    /**
     * Get exam note result
     * @param $notes[], array notes of selected mention/niveau/parcours from current univ year
     * @return mixed data
     * */

    public function getClassExamenNote($notes){
        $semResults = $this::getClassNoteSemestre($notes);
        $finalResults = [];
        foreach($semResults as $key => $itemRes) {
            $currentResult = [];
            $currentResult['etudiantId']  = $key;
            $currentResult['firstName']   = $itemRes['firstName'];
            $currentResult['lastName']    = $itemRes['lastName'];
            $semResultItem = array_values($itemRes['semestre']);
            if( count($semResultItem) < 2 )
                return $finalResults;
            $semResultItem1 = $semResultItem[0];
            $semResultItem2 = $semResultItem[1];
            // dump($semResultItem);die;
        
            $iMoyenneGen = round(
                ($semResultItem1['moyenneGenerale'] + $semResultItem2['moyenneGenerale']) / 2, 2, PHP_ROUND_HALF_DOWN
            );
            $currentResult['iCreditUeTransversale']     = $semResultItem1['iCreditUeTransversale']      + $semResultItem2['iCreditUeTransversale'];
            $currentResult['iCreditUeComplementaire']   = $semResultItem1['iCreditUeComplementaire']    + $semResultItem2['iCreditUeComplementaire'];
            $currentResult['iCreditUeFondamental']      = $semResultItem1['iCreditUeFondamental']       + $semResultItem2['iCreditUeFondamental'];
            $currentResult['iCrdFondamentaleObtenu']    = $semResultItem1['iCrdFondamentaleObtenu']     + $semResultItem2['iCrdFondamentaleObtenu'];
            $currentResult['iCrdTransversaleObtenu']    = $semResultItem1['iCrdTransversaleObtenu']     + $semResultItem2['iCrdTransversaleObtenu'];
            $currentResult['iCrdComplementaireObtenu']  = $semResultItem1['iCrdComplementaireObtenu']   + $semResultItem2['iCrdComplementaireObtenu'];
            $currentResult['iTotalCreditObtenu']        = $semResultItem1['iTotalCreditObtenu']         + $semResultItem2['iTotalCreditObtenu'];
            $currentResult['iTotalCredit']              = $semResultItem1['iTotalCredit']               + $semResultItem2['iTotalCredit'];

            $currentResult['iCrdTransversaleFinal']    = $semResultItem1['iCrdTransversaleFinal']       + $semResultItem2['iCrdTransversaleFinal'];
            $currentResult['iCrdComplementaireFinal']  = $semResultItem1['iCrdComplementaireFinal']     + $semResultItem2['iCrdComplementaireFinal'];

            $currentResult['iTotalCreditFinal']         = $semResultItem1['iTotalCreditFinal']          + $semResultItem2['iTotalCreditFinal'];

            if($iMoyenneGen >= 10) {
                $currentResult['iCrdTransversaleFinal']    = $currentResult['iCreditUeTransversale'];
                $currentResult['iCrdComplementaireFinal']  = $currentResult['iCreditUeComplementaire'];
                $currentResult['iTotalCreditFinal']       = $currentResult['iCrdFondamentaleObtenu'] + $currentResult['iCrdTransversaleFinal'] + $currentResult['iCrdComplementaireFinal'];
            } 
            $currentResult['resultStatut'] = '';
            if($iMoyenneGen < 10)
                $resultStatut = 'AJ';
            if(
                $iMoyenneGen >= 10 && 
                ($currentResult['iTotalCredit'] == $currentResult['iTotalCreditObtenu'])
            )
                $resultStatut = 'ADM';
            if(
                $iMoyenneGen >= 10 && 
                ($currentResult['iTotalCredit'] > $currentResult['iTotalCreditObtenu']) && 
                ($currentResult['iCrdFondamentaleObtenu'] == $currentResult['iCreditUeFondamental'])
            )
                $resultStatut = 'ADC';
            if(
                $iMoyenneGen >= 10 && 
                ($currentResult['iTotalCredit'] > $currentResult['iTotalCreditObtenu']) && 
                ($currentResult['iCreditUeFondamental'] > $currentResult['iCrdFondamentaleObtenu'])
            )
                $resultStatut = 'AJAC';

            $currentResult['moyenneGenerale'] = $iMoyenneGen;
            $currentResult['resultStatut'] = $resultStatut;
            $finalResults[$key] = $currentResult;
        }
        // dump($finalResults);die;
        $tMoyenneGfilter = array_column($finalResults, 'moyenneGenerale');
        array_multisort($tMoyenneGfilter, SORT_DESC, $finalResults);

        return $finalResults;
    }   
}