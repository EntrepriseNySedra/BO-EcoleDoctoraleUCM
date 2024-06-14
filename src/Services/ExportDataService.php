<?php

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\CalendrierPaiement;
use App\Entity\FraisScolarite;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Description of ExportDataService.php.
 *
 * @package App\Services
 */
class ExportDataService
{

    public $oSpreadsheet;
    public $oSheet;
    public $oWriter;
    public $oFile;
    public $filepath;
    public $filename;
    public $head;
    public $columnHeadName;
    const SPREED_COLUMN_NAME = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    public function __construct() {
        $this->oSpreadsheet = new Spreadsheet();
        $this->oSheet = $this->oSpreadsheet->getActiveSheet();
        $this->oWriter = new Xlsx($this->oSpreadsheet);
        
    }

    public function writeFile() {
        $file = $this->filepath . '/' . $this->filename;
        $this->oWriter->save($file);    
        $this->oFile = new File($file);
    }

    public function writeHead($row) {
        $this->_setColumnHead();        
        foreach($this->head as $key => $value) {
             $this->oSheet->setCellValue("{$this->columnHeadName[$key]}{$row}", $value);
        }
    }

    public function writeCell($cellValue, $row) {
        foreach($this->head as $key => $value) {
            $currentColRef = $this->columnHeadName[$key];
            $this->oSheet->setCellValue("{$currentColRef}{$row}", $cellValue[$key]);
        }
    }

    private function _setColumnHead() {
        $this->columnHeadName = self::SPREED_COLUMN_NAME;
    
        if (count($this->head) > count($this->columnHeadName)) {
            $initColName = $this->columnHeadName[0];
            $newColumnHeadName = array_map(
                function($item) use ($initColName) {
                    return $initColName . $item;
                },
                $this->columnHeadName
            );
            $this->columnHeadName = array_merge($this->columnHeadName, $newColumnHeadName);
        }
    }

    /**
     * @param mixed data $list
     * @param $period
     * @param ParamterBagInterface $parameter
     * @return $file
     */
    public function getPaiementEcolage($list, $period, $parameter,$status)
    {
        $racineDirectory     = $parameter->get('ecolage_export_directory');
        $currPeriodDir = $racineDirectory . '/' .trim($period->getLibelle());
        if(!is_dir($currPeriodDir)){
            try {
                $fileSystem = new Filesystem();
                $fileSystem->mkdir($currPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currPeriodDir;        
        $this->filename = '/ecolage.xlsx';

        $row = 1;
        // $this->oSheet->setCellValue("A{$row}", 'Paiement ecolage ' . $period->getDateDebut()->format('d/m/Y') . ' au ' . $period->getDateFin()->format('d/m/Y') );
        $this->oSheet->setCellValue("A{$row}", 'Paiement ecolage');
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:I{$row}");
        $row++;
        $this->oSheet->setCellValue("A{$row}", 'Date de versement');
        $this->oSheet->setCellValue("B{$row}", 'N° reçu de versement');
        $this->oSheet->setCellValue("C{$row}", 'Classe');
        $this->oSheet->setCellValue("D{$row}", 'Tranche');
        $this->oSheet->setCellValue("E{$row}", 'Montant tranche');
        $this->oSheet->setCellValue("F{$row}", 'Matricule');
        $this->oSheet->setCellValue("G{$row}", 'Nom');
        $this->oSheet->setCellValue("H{$row}", 'Prénom');
        $this->oSheet->setCellValue("I{$row}", 'Date de saisie');
        $this->oSheet->setCellValue("J{$row}", 'Montant versé');
        $this->oSheet->setCellValue("K{$row}", 'Reste');
        $this->oSheet->setCellValue("L{$row}", 'Mode de paiement');
        $this->oSheet->setCellValue("M{$row}", 'Nom du remettant');
        $row++;
        foreach($list as $item) {
        //   if ($status == FraisScolarite::STATUS_CREATED && $item['status'] == FraisScolarite::STATUS_CREATED) {
            $this->oSheet->setCellValue("A{$row}", $item['date_paiement']);
            $this->oSheet->setCellValue("B{$row}", $item['reference']);
            $this->oSheet->setCellValue("C{$row}", $item['mention'] . " " . $item['niveau'] . " " . $item['parcours']);
            $this->oSheet->setCellValue("D{$row}", $item['semestre']);
            $this->oSheet->setCellValue("E{$row}", number_format($item['montant_tranche'], 2, '.', ' '));
            $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
            $this->oSheet->setCellValue("G{$row}", $item['last_name']);
            $this->oSheet->setCellValue("H{$row}", $item['first_name']);
            $this->oSheet->setCellValue("I{$row}", $item['date_saisie']);
            $this->oSheet->setCellValue("J{$row}", number_format($item['montant'], 2, '.', ' '));
            $this->oSheet->setCellValue("K{$row}", number_format($item['reste'], 2, '.', ' '));
            switch($item['mode_paiement']){
                case FraisScolarite::MODE_PAIEMENT_VIREMENT: 
                    $mode_paiement = 'Virement';
                    break;
                case FraisScolarite::MODE_PAIEMENT_AGENCE: 
                    $mode_paiement = 'Chèque';
                    break;
                case FraisScolarite::MODE_PAIEMENT_CAISSE: 
                    $mode_paiement = 'Espèces';
                    break;
            }
            $this->oSheet->setCellValue("L{$row}", $mode_paiement);
            $this->oSheet->setCellValue("M{$row}", $item['remitter']);

            $row++;
        //}

        // elseif ($status == FraisScolarite::STATUS_SRS_VALIDATED && $item['status'] == FraisScolarite::STATUS_SRS_VALIDATED){
        //     $this->oSheet->setCellValue("A{$row}", $item['date_paiement']);
        //     $this->oSheet->setCellValue("B{$row}", $item['reference']);
        //     $this->oSheet->setCellValue("C{$row}", $item['mention'] . " " . $item['niveau'] . " " . $item['parcours']);
        //     $this->oSheet->setCellValue("D{$row}", $item['semestre']);
        //     $this->oSheet->setCellValue("E{$row}", number_format($item['montant_tranche'], 2, '.', ' '));
        //     $this->oSheet->setCellValue("F{$row}", $item['immatricule']);
        //     $this->oSheet->setCellValue("G{$row}", $item['last_name']);
        //     $this->oSheet->setCellValue("H{$row}", $item['first_name']);
        //     $this->oSheet->setCellValue("I{$row}", $item['date_saisie']);
        //     $this->oSheet->setCellValue("J{$row}", number_format($item['montant'], 2, '.', ' '));
        //     $this->oSheet->setCellValue("K{$row}", number_format($item['reste'], 2, '.', ' '));
        //     switch($item['mode_paiement']){
        //         case FraisScolarite::MODE_PAIEMENT_VIREMENT: 
        //             $mode_paiement = 'Virement';
        //             break;
        //         case FraisScolarite::MODE_PAIEMENT_AGENCE: 
        //             $mode_paiement = 'Chèque';
        //             break;
        //         case FraisScolarite::MODE_PAIEMENT_CAISSE: 
        //             $mode_paiement = 'Espèces';
        //             break;
        //     }
        //     $this->oSheet->setCellValue("L{$row}", $mode_paiement);
        //     $this->oSheet->setCellValue("M{$row}", $item['remitter']);

        //     $row++;
        // }

        }        
        $this->writeFile();        

        return $this->oFile;
    }

    /**
     * @param mixed data $list
     * @param AnneeUniversitaire $anneeUniversitaire
     * @param ParameterBagInterface $parameter
     * @return $file
     */
    public function getEtudiantInscrit($list, AnneeUniversitaire $anneeUniversitaire, $parameter)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $sheet->setCellValue("A{$row}", 'Liste des étudiants inscrits de l\'année ' . $anneeUniversitaire->getLibelle());
        $spreadsheet->getActiveSheet()->mergeCells("A{$row}:V{$row}");
        $row++;
        $sheet->setCellValue("A{$row}", 'Matricule');
        $sheet->setCellValue("B{$row}", 'Nom');
        $sheet->setCellValue("C{$row}", 'Prénom');

        $sheet->setCellValue("D{$row}", 'Mention');
        $sheet->setCellValue("E{$row}", 'Niveau');
        $sheet->setCellValue("F{$row}", 'Parcours');

        $sheet->setCellValue("G{$row}", 'Nationalité');
        $sheet->setCellValue("H{$row}", 'Civilité');
        $sheet->setCellValue("I{$row}", 'Né le');
        $sheet->setCellValue("J{$row}", 'à');
        $sheet->setCellValue("K{$row}", 'CIN num');
        $sheet->setCellValue("L{$row}", 'Délivré le');
        $sheet->setCellValue("M{$row}", 'à');
        $sheet->setCellValue("N{$row}", 'Adresse');
        $sheet->setCellValue("O{$row}", 'Email');
        $sheet->setCellValue("P{$row}", 'Téléphone');
        $sheet->setCellValue("Q{$row}", 'Réligion');
        $sheet->setCellValue("R{$row}", 'Nom père');
        $sheet->setCellValue("S{$row}", 'Profesion père');
        $sheet->setCellValue("T{$row}", 'Adresse père');
        $sheet->setCellValue("U{$row}", 'Contact père');
        $sheet->setCellValue("V{$row}", 'Nom mère');
        $sheet->setCellValue("W{$row}", 'Adresse mère');
        $sheet->setCellValue("X{$row}", 'Profession mère');
        $sheet->setCellValue("Y{$row}", 'Contact mère');
        $row++;
        foreach($list as $item) {        
            $sheet->setCellValue("A{$row}", $item['immatricule']);
            $sheet->setCellValue("C{$row}", $item['last_name']);
            $sheet->setCellValue("B{$row}", $item['first_name']);

            $sheet->setCellValue("D{$row}", $item['mention']);
            $sheet->setCellValue("E{$row}", $item['niveau']);
            $sheet->setCellValue("F{$row}", $item['parcours']);
            
            $sheet->setCellValue("G{$row}", $item['nationality']);
            $sheet->setCellValue("H{$row}", $item['civility']);
            $sheet->setCellValue("I{$row}", $item['birth_date']);
            $sheet->setCellValue("J{$row}", $item['birth_place']);
            $sheet->setCellValue("K{$row}", $item['cin_num']);
            $sheet->setCellValue("L{$row}", $item['cin_delivery_date']);
            $sheet->setCellValue("M{$row}", $item['cin_delivery_location']);
            $sheet->setCellValue("N{$row}", $item['address']);
            $sheet->setCellValue("O{$row}", $item['email']);
            $sheet->setCellValue("P{$row}", $item['phone']);
            $sheet->setCellValue("Q{$row}", $item['religion']);
            $sheet->setCellValue("R{$row}", $item['father_name']);
            $sheet->setCellValue("S{$row}", $item['father_job']);
            $sheet->setCellValue("T{$row}", $item['father_address_contact']);
            $sheet->setCellValue("U{$row}", $item['father_job_address_contact']);
            $sheet->setCellValue("V{$row}", $item['mother_name']);
            $sheet->setCellValue("W{$row}", $item['mother_job']);
            $sheet->setCellValue("X{$row}", $item['mother_address_contact']);
            $sheet->setCellValue("Y{$row}", $item['mother_job_address_contact']);
            
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $racineDirectory     = $parameter->get('inscription_export_directory');
        $currentPeriodDir = $racineDirectory .'/'. $anneeUniversitaire->getLibelle();
        if(!is_dir($currentPeriodDir)){
            try {
                $fileSystem = new Filesystem();
                $fileSystem->mkdir($currentPeriodDir);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $writer->save($currentPeriodDir . "/inscription.xlsx");
        $file = new File($currentPeriodDir . '/inscription.xlsx');

        return $file;
    }
}