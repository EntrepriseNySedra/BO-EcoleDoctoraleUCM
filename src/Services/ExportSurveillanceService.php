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
class ExportSurveillanceService extends ExportDataService
{

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getVacationCompta($data, CalendrierPaiement $period, $parameter, $indexNumPiece){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/surveillance/';
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
        $this->filename = "surveillance-compta.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Surveillance compta du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
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
    public function getEtatPaiement($data, CalendrierPaiement $period, $parameter){
        $racineDirectory     = $parameter->get('vacation_export_directory');
        $currentDetailDir = $racineDirectory . '/surveillance/';
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
        $this->filename = "surveillance-etat.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Surveillance etat');
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;

        $this->head = ['Matricule', 'Nom', 'Matieres', 'TOT.H EFF', 'Taux horaire', 'Montant en ar', 'IRSA', 'Montant NET','Signature'];
        $this->writeHead($row);
        $row++;
        foreach($data as $item) {
            $this->writeLineEtatPaiement($item, $parameter, $row);
            $row++;
        }
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * Write vacation compta line 
     **/
    private function writeLineVacationCompta($item, $period, $parameter, &$row, $indexNumPiece) {
        $impot = $parameter->get('impot');
        $tauxSurveillance = $parameter->get('taux_surveillant');
        $montantHT = $item['totalQte'] * $tauxSurveillance;
        $montantTTC = $montantHT / 0.98;
        $montantImpot = $montantTTC * $impot;
        
        $numCompteGeneral = [$parameter->get('num_compte_general_1'), $parameter->get('num_compte_general_2'), $item['num_compte_generale']];
        foreach($numCompteGeneral as $key => $value) {
            $this->oSheet->setCellValue("A{$row}", 'SUR');
            $this->oSheet->setCellValue("B{$row}", $period->getDateFin()->format('d/m/y'));
            $this->oSheet->setCellValue("C{$row}", $indexNumPiece);
            $this->oSheet->setCellValue("D{$row}", $item['niveau'].' '.$item['mention']);
            $this->oSheet->setCellValue("E{$row}", $period->getLibelle());
            $this->oSheet->setCellValue("F{$row}", $value);
            $this->oSheet->setCellValue("G{$row}", '');
            if($key == 2)
                $this->oSheet->setCellValue("G{$row}", $item['tiers_num']);
            $this->oSheet->setCellValue("H{$row}", $item['last_name'].' '.$item['first_name']);
            $this->oSheet->setCellValue("I{$row}", '');
            if($key == 0)
                $this->oSheet->setCellValue("I{$row}", number_format($montantHT, 2, '.', ' '));
            $this->oSheet->setCellValue("J{$row}", '');
            if($key == 1)
                $this->oSheet->setCellValue("J{$row}", number_format($montantImpot, 2, '.', ' '));
            if($key == 2)
                $this->oSheet->setCellValue("J{$row}", number_format($montantTTC, 2, '.', ' '));
            $row++;
        }
    }

    /**
     * Write Etat paiement line 
     **/
    private function writeLineEtatPaiement($item, $parameter, &$row) {
        $impot = $parameter->get('impot');
        $tauxSurveillance = $parameter->get('taux_surveillant')/0.98;
        $montantTTC = $item['totalHeure'] * $tauxSurveillance;
        $montantImpot = $montantTTC * $impot;
        $montantHT = $montantTTC - $montantImpot;
        $this->oSheet->setCellValue("A{$row}", $item['matricule']);
        $this->oSheet->setCellValue("B{$row}", $item['surveillantName']);
        $this->oSheet->setCellValue("C{$row}", $item['matieres']);
        $this->oSheet->setCellValue("D{$row}", $item['totalHeure']);
        $this->oSheet->setCellValue("E{$row}", number_format($tauxSurveillance, 2, '.', ' '));
        $this->oSheet->setCellValue("F{$row}", number_format($montantTTC, 2, '.', ' '));
        $this->oSheet->setCellValue("G{$row}", number_format($montantImpot, 2, '.', ' '));
        $this->oSheet->setCellValue("H{$row}", number_format($montantHT, 2, '.', ' '));        
    }
}