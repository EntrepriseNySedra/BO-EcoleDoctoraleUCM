<?php

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\CalendrierPaiement;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of ExportVacationService.php.
 *
 * @package App\Services
 */
class ExportVacationService extends ExportDataService
{

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getVacationDetails($data, CalendrierPaiement $period, $parameter){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/details/';
        $fileSystem = new Filesystem();
        if(!is_dir($currentDetailDir)){
            try {
                $fileSystem->mkdir($currentDetailDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }            
        }
        $currentPeriodDir = $currentDetailDir . trim($period->getLibelle());
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentPeriodDir;
        $this->filename = "vacation-details.xlsx";
        $impot = $parameter->get('impot');
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Etat des vacations du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;
        foreach($data as $mention => $menVal) {
            $this->oSheet->setCellValue("A{$row}", $mention);    
            $row++;

            foreach($menVal as $niveau => $nivVal) {
                $this->oSheet->setCellValue("A{$row}", $niveau);
                $this->oSheet->setCellValue("B{$row}", '');
                $this->oSheet->setCellValue("C{$row}", '');
                $this->oSheet->setCellValue("D{$row}", 'Début');
                $this->oSheet->setCellValue("E{$row}", 'Fin');
                $this->oSheet->setCellValue("F{$row}", 'Heure');
                $this->oSheet->setCellValue("G{$row}", 'Taux');
                $this->oSheet->setCellValue("H{$row}", 'Montant');
                $this->oSheet->setCellValue("I{$row}", 'Taux impôt');
                $this->oSheet->setCellValue("J{$row}", 'Impôt');
                $this->oSheet->setCellValue("K{$row}", 'Montant net');
                $row++;
                foreach($nivVal as $ensId => $itemVal) {
                    $this->oSheet->setCellValue("A{$row}", $itemVal[0]['immatricule']);
                    $this->oSheet->setCellValue("B{$row}", $itemVal[0]['first_name']. ' ' . $itemVal[0]['last_name']);
                    $row++;
                    foreach($itemVal as $item) {
                        $heure = $item['heure'];
                        $taux = floatval($item['taux_horaire']);
                        $montant = ($taux * $heure) / 0.98 ;
                        $montantImpot = floatval($montant - ($taux * $heure));
                        $this->oSheet->setCellValue("A{$row}", $item['immatricule']);
                        $this->oSheet->setCellValue("B{$row}", $item['date_schedule']);
                        $this->oSheet->setCellValue("C{$row}", $item['matiere']);
                        $this->oSheet->setCellValue("D{$row}", $item['start_time']);
                        $this->oSheet->setCellValue("E{$row}", $item['end_time']);
                        $this->oSheet->setCellValue("F{$row}", $item['heure']);
                        $this->oSheet->setCellValue("G{$row}", number_format($taux, 2, '.', ' '));
                        $this->oSheet->setCellValue("H{$row}", number_format($montant, 2, '.', ' '));
                        $this->oSheet->setCellValue("I{$row}", number_format($impot * 100, 2, '.', ' ') . '%');
                        $this->oSheet->setCellValue("J{$row}", number_format($montantImpot, 2, '.', ' '));
                        $this->oSheet->setCellValue("K{$row}", number_format($montant - $montantImpot, 2, '.', ' '));
                        $row++;
                    }
                }
            }
        }
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getVacationCompta($data, CalendrierPaiement $period, $parameter, $indexNumPiece){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/compta/';
        if(!is_dir($currentDetailDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentDetailDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $currentPeriodDir = $currentDetailDir . trim($period->getLibelle());
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentPeriodDir;
        $this->filename = "vacation-compta.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Vacation compta du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;

        $this->head = ['Code journal', 'Date', 'N° pièce', 'N° Facture', 'Référence', 'N° Compte général', 'N° Compte tiers', 'Libellés', 'Débit', 'Crédit'];
        $this->writeHead($row);
        $row++;
        foreach($data as $item) {
            $this->writeLineVacationCompta($item, $period, $parameter, $row, $indexNumPiece);
            $indexNumPiece++;
        }
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getVacationBank($data, CalendrierPaiement $period, $parameter, $indexNumPiece){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/compta/';
        if(!is_dir($currentDetailDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentDetailDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $currentPeriodDir = $currentDetailDir . trim($period->getLibelle());
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentPeriodDir;
        $this->filename = "vacation-bank.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Compta banque du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;

        $this->head = ['Code journal', 'Date', 'N° pièce', 'N° Facture', 'Référence', 'N° Compte général', 'N° Compte tiers', 'Libellés', 'Crédit'];
        $this->writeHead($row);
        $row++;
        $montantTotal = 0;
        foreach($data as $item) {
             
            $currentMontantTTC = $this->writeLineVacationBank($item, $period, $parameter, $row, $indexNumPiece);
            $montantTotal += $currentMontantTTC;
            $row++;
        }
        $this->writeLineVacationBankTotal($montantTotal, $period, $parameter, $row);
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getVacationOpavi($data, CalendrierPaiement $period, $parameter, $indexNumPiece){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/compta/';
        if(!is_dir($currentDetailDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentDetailDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $currentPeriodDir = $currentDetailDir . trim($period->getLibelle());
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentPeriodDir;
        $this->filename = "vacation-opavi.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'OPAVI du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;
        $rowHead = $row;
        $totalCob = 0;
        $row++;
        foreach($data as $item) {
            $this->writeLineVacationOpavi($item, $period, $parameter, $row, $indexNumPiece, $totalCob);
            $row++;
            $indexNumPiece++;
        }

        $this->writeOpaviHead($parameter, $rowHead, $totalCob);
        $this->writeHead($rowHead);
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * Write vacation opavi head 
     **/
    private function writeOpaviHead($parameter, $rowHead, $totalCob) {
        $this->head = [$parameter->get('opavi_col1_head'), number_format($totalCob, 2, ',', ' '), $parameter->get('opavi_col3head'), 'Vacation du période'];
    }

    /**
     * Write vacation compta line 
     **/
    private function writeLineVacationBank($item, $period, $parameter, &$row, $indexNumPiece) {
        $impot = $parameter->get('impot');
        $montantHT = $item['montantHT'];
        $montantTTC = $montantHT / (0.98);       
        $montantImpot = floatval($montantTTC - $montantHT);
        
        $this->oSheet->setCellValue("A{$row}", 'BQ3');
        $this->oSheet->setCellValue("B{$row}", date('d/m/Y'));
        $this->oSheet->setCellValue("C{$row}", $indexNumPiece);
        $this->oSheet->setCellValue("D{$row}", $item['niveau'].' '.$item['mention']);
        $this->oSheet->setCellValue("E{$row}", $period->getLibelle());
        $this->oSheet->setCellValue("F{$row}", $item['num_compte_generale']);
        $this->oSheet->setCellValue("G{$row}", $item['tiers_count']);
        $this->oSheet->setCellValue("H{$row}", $item['last_name'].' '.$item['first_name']);    
        $this->oSheet->setCellValue("I{$row}", number_format($montantHT, 2, '.', ' '));

        return $montantTTC;
    }

    /**
     * Write vacation compta line 
     **/
    private function writeLineVacationBankTotal($total, $period, $parameter, &$row) {
        $this->oSheet->setCellValue("A{$row}", 'BQ3');
        $this->oSheet->setCellValue("B{$row}", "");
        $this->oSheet->setCellValue("C{$row}", "");
        $this->oSheet->setCellValue("D{$row}", "");
        $this->oSheet->setCellValue("E{$row}", "");
        $this->oSheet->setCellValue("F{$row}", $parameter->get('num_compte_general_bank'));
        $this->oSheet->setCellValue("G{$row}", "");
        $this->oSheet->setCellValue("H{$row}", "Virement vacation " . $period->getLibelle());    
        $this->oSheet->setCellValue("I{$row}", "");
        $this->oSheet->setCellValue("J{$row}", number_format($total, 2, '.', ' '));
    }

    /**
     * Write vacation compta line 
     **/
    private function writeLineVacationOpavi($item, $period, $parameter, &$row, $indexNumPiece, &$totalCob) {
        $impot = $parameter->get('impot');
        $montantHT = $item['montantHT'];
        $montantTTC = $montantHT / 0.98;
        $montantImpot = floatval($montantTTC - $montantHT);
        
        $totalCob += $montantHT;
       
        $this->oSheet->setCellValue("A{$row}", $item['bank_num']);
        $this->oSheet->setCellValue("B{$row}", $montantHT);
        $this->oSheet->setCellValue("C{$row}", $item['tiers_count']);
        $this->oSheet->setCellValue("D{$row}", $item['last_name'].' '.$item['first_name']);    
    }

    /**
     * Write vacation compta line 
     **/
    private function writeLineVacationCompta($item, $period, $parameter, &$row, $indexNumPiece) {
        $impot = $parameter->get('impot');
        $montantHT = $item['montantHT'];
        $montantTTC = $montantHT / 0.98;
        $montantImpot = floatval($montantTTC - $montantHT);
        
        $numCompteGeneral = [$parameter->get('num_compte_general_1'), $parameter->get('num_compte_general_2'), $item['num_compte_generale']];
        foreach($numCompteGeneral as $key => $value) {
            $this->oSheet->setCellValue("A{$row}", 'VAC');
            $this->oSheet->setCellValue("B{$row}", $period->getDateFin()->format('d/m/y'));
            $this->oSheet->setCellValue("C{$row}", $indexNumPiece);
            $this->oSheet->setCellValue("D{$row}", $item['niveau'].' '.$item['mention']);
            $this->oSheet->setCellValue("E{$row}", $period->getLibelle());
            $this->oSheet->setCellValue("F{$row}", $value);
            $this->oSheet->setCellValue("G{$row}", '');
            if($key == 2)
                $this->oSheet->setCellValue("G{$row}", $item['tiers_count']);
            $this->oSheet->setCellValue("H{$row}", $item['last_name'].' '.$item['first_name']);
            $this->oSheet->setCellValue("I{$row}", '');
            if($key == 0)
                $this->oSheet->setCellValue("I{$row}", number_format($montantTTC, 2, '.', ' '));
            $this->oSheet->setCellValue("J{$row}", '');
            if($key == 1)
                $this->oSheet->setCellValue("J{$row}", number_format($montantImpot, 2, '.', ' '));
            if($key == 2)
                $this->oSheet->setCellValue("J{$row}", number_format($montantHT, 2, '.', ' '));
            $row++;
        }
    }
}