<?php

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\BankCompte;
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
class ExportEcolageService extends ExportDataService
{
    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getEtatBanque($data, CalendrierPaiement $period, $parameter, $np){
        $racineDirectory     = $parameter->get('ecolage_export_directory');
        $currentDetailDir = $racineDirectory . '/banque/';
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
        $this->filename = "etat-banque.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Etat banque du ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;

        $this->head = ['JOUR', 'N° pièce', 'N° Facture', 'Référence', 'N° Compte', 'Matricule', 'LIBELLE ECRITURE', 'DEBIT', 'CREDIT'];
        $this->writeHead($row);
        $row++;
        foreach($data as $item) {
            $this->writeLineEtatBank($item, $period, $parameter, $row, $np);
        }
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * @param mixed $data
     * @param ParameterBagInterface $parameter
     */
    public function getEtatVente($data, $currentAnneUniv, $parameter, $np){
        $racineDirectory     = $parameter->get('ecolage_export_directory');
        $currentDetailDir = $racineDirectory . '/vente/';
        if(!is_dir($currentDetailDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentDetailDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $currentPeriodDir = $currentDetailDir . trim($currentAnneUniv->getLibelle());
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem = new FileSystem();
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentPeriodDir;
        $this->filename = "etat-vente.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Etat vente du ' . $currentAnneUniv->getLibelle());
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:K{$row}");
        $row++;

        $this->head = ['JOUR', 'N° pièce', 'N° Facture', 'Référence', 'N° Compte', 'Matricule', 'LIBELLE ECRITURE', 'DATE ECHEANCE', 'POSITION JOURNAL', 'DEBIT', 'CREDIT'];
        $this->writeHead($row);
        $row++;
        foreach($data as $item) {
            $this->writeLineEtatVente($item, $row, $np);
        }
        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * Write Ecolage bank line 
     **/
    private function writeLineEtatBank($item, $period, $parameter, &$row, $np) {
        $date = date_create($item['date_paiement']);        
        $this->oSheet->setCellValue("A{$row}", date_format($date,"d/m/Y"));
        $this->oSheet->setCellValue("B{$row}", $np);
        $this->oSheet->setCellValue("C{$row}", $item['mention'].$item['niveau'] . '-'. $item['semestre']);
        $this->oSheet->setCellValue("D{$row}", $item['reference']);
        $this->oSheet->setCellValue("E{$row}", $item['compte_number']);
        $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
        $this->oSheet->setCellValue("G{$row}", $item['last_name']. ' ' .$item['first_name']);
        $this->oSheet->setCellValue("H{$row}", '');
        $this->oSheet->setCellValue("I{$row}", number_format($item['montant'], 2, '.', ' '));        
        $row++;
        $this->oSheet->setCellValue("A{$row}", date_format($date,"d/m/Y"));
        $this->oSheet->setCellValue("B{$row}", $np);
        $this->oSheet->setCellValue("C{$row}", $item['mention'].$item['niveau'] . '-'. $item['semestre']);
        $this->oSheet->setCellValue("D{$row}", $item['reference']);
        $this->oSheet->setCellValue("E{$row}", $parameter->get('num_compte_general_bank'));
        // $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
        $this->oSheet->setCellValue("F{$row}", '');
        $this->oSheet->setCellValue("G{$row}", $item['last_name']. ' ' .$item['first_name']);
        $this->oSheet->setCellValue("H{$row}", number_format($item['montant'], 2, '.', ' '));
        $this->oSheet->setCellValue("I{$row}", '');
        $row++;
    }

    /**
     * Write Ecolage bank line 
     **/
    private function writeLineEtatVente($item, &$row, $np) {
        $date = '30/10'.date('Y');        
        // dump($item);
        if($item['resource'] == BankCompte::ECOLAGE) {
            $this->oSheet->setCellValue("A{$row}", $date);
            $this->oSheet->setCellValue("B{$row}", $np);
            $this->oSheet->setCellValue("C{$row}", $item['mention'].$item['niveau']);
            $this->oSheet->setCellValue("D{$row}", '');
            $this->oSheet->setCellValue("E{$row}", $item['number']);
            $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
            $this->oSheet->setCellValue("G{$row}", $item['last_name']. ' ' .$item['first_name']);
            $this->oSheet->setCellValue("H{$row}", '');
            $this->oSheet->setCellValue("I{$row}", '');
            $this->oSheet->setCellValue("J{$row}", number_format($item['ecolage'], 2, '.', ' '));
            $this->oSheet->setCellValue("K{$row}", '');   
        }    
        if($item['resource'] == BankCompte::FRAIS_SCOLARITE) {
            $this->oSheet->setCellValue("A{$row}", $date);
            $this->oSheet->setCellValue("B{$row}", $np);
            $this->oSheet->setCellValue("C{$row}", $item['mention'].$item['niveau']);
            $this->oSheet->setCellValue("D{$row}", '');
            $this->oSheet->setCellValue("E{$row}", $item['number']);
            $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
            $this->oSheet->setCellValue("G{$row}", $item['last_name']. ' ' .$item['first_name']);
            $this->oSheet->setCellValue("H{$row}", '');
            $this->oSheet->setCellValue("I{$row}", '');
            $this->oSheet->setCellValue("J{$row}", '');
            $this->oSheet->setCellValue("K{$row}", number_format($item['ecolage'], 2, '.', ' '));
        }
        $row++;
    }
}