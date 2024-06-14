<?php

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\ConcoursCandidature;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of ExportConcoursService.php.
 *
 * @package App\Services
 */
class ExportConcoursService extends ExportDataService
{

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getCandidature($data, $options, $parameter){
        $anneeUniversitaire = $options['anneeUniversitaire'];
        $mention = isset($options['mention']) ? $options['mention'] : '';
        $niveau  = isset($options['niveau']) ? $options['niveau'] : '';
        $status  = $options['status'];
        $parcours = "";
        $racineDirectory     = $parameter->get('concours_directory');
        $filesystem = new Filesystem();
        $currentDetailDir = $racineDirectory . '/' . $anneeUniversitaire->getAnnee() . '/candidatures-admis';
        if(!is_dir($currentDetailDir)){
            try {
                $filesystem->mkdir($currentDetailDir);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $currentClassDir = $currentDetailDir . '/'. trim(($niveau ? $niveau->getLibelle(): '') . '-' . ($mention ? $mention->getDiminutif() : ''));
        if(!is_dir($currentClassDir)){
            try {
                $filesystem->mkdir($currentClassDir);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $currentClassDir;
        $this->filename = "candidatures.xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'List des candidatures '. $this->getStatusLibelle($status) .' au concours ' . ($niveau ? $niveau->getLibelle():''). ' ' . ($mention ? $mention->getNom():''));
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:Z{$row}");
        $row++;

        $this->head = ['Matricule', 'Nom', 'Prénom', 'Mention', 'Niveau', 'Parcours', 'Centre', 'Email', 'Date de naissance', 'Lieu de naissance', 'Diplôme', 'Nationalité', 'CIN', 'Délivré le', 'Adresse', 'Téléphone', 'Réligion', 'Profession', 'Réf paiement', 'Date paiement', 'Nom conjoint(e)', 'Adresse conjoint(e)', 'Contact conjoint(e)', 'Profession conjoint(e)', 'Nom père', 'Adresse père', 'Contact père', 'Profession père', 'Nom mère', 'Adresse mère', 'Contact mère', 'Profession mère', 'Tuteur', 'Adresse Tuteur', 'Contact Tuteur', 'Profession Tuteur', 'Profil réligieux', 'Congrégation réligieuse', 'Resp foyer réligieuse', 'Contact resp religieuse', 'Email respo religieuse', 'Série bacc', 'Année bacc', 'Num inscription bacc', 'Autre non bacc', 'Autre Série bacc', 'Mention bacc'];
        $this->writeHead($row);
        $cellValue = [];
        $row++;

        // foreach($data as $key => $item) {    
        //     $this->oSheet->setCellValue("{$this->columnHeadName[$key]}{$row}", $item->getImmatricule());
        //     $this->oSheet->setCellValue("B{$row}", $item->getLastName());
        //     $this->oSheet->setCellValue("C{$row}", $item->getFirstName());
        //     $this->oSheet->setCellValue("D{$row}", $item->getMention()->getNom());
        //     $this->oSheet->setCellValue("F{$row}", $item->getNiveau()->getLibelle());
        //     $this->oSheet->setCellValue("F{$row}", $item->getParcours() ? $item->getParcours()->getNom(): '');
        //     $this->oSheet->setCellValue("G{$row}", $item->getEmail());
        //     $this->oSheet->setCellValue("H{$row}", $item->getDateOfBirth()->format('d/m/Y'));
        //     $this->oSheet->setCellValue("I{$row}", $item->getBirthPlace());
        //     $this->oSheet->setCellValue("J{$row}", $item->getDiplome());
        //     $this->oSheet->setCellValue("K{$row}", $item->getNationality());
        //     $this->oSheet->setCellValue("L{$row}", $item->getCinNum());            
        //     $this->oSheet->setCellValue("M{$row}", $item->getCinDeliverDate() ? $item->getCinDeliverDate()->format('d/m/Y'): "");
        //     $this->oSheet->setCellValue("N{$row}", $item->getAddress());
        //     $this->oSheet->setCellValue("O{$row}", $item->getPhone1());
        //     $this->oSheet->setCellValue("P{$row}", $item->getConfessionReligieuse());
        //     $this->oSheet->setCellValue("Q{$row}", $item->getJob());
        //     $this->oSheet->setCellValue("R{$row}", $item->getPayementRef());
        //     $this->oSheet->setCellValue("S{$row}", $item->getPaymentDate() ? $item->getPaymentDate()->format('d/m/Y'): "");
        //     $this->oSheet->setCellValue("T{$row}", $item->getConjointFirstName() . ' ' . $item->getConjointLastName());


        //     $this->oSheet->setCellValue("R{$row}", $item->getConjointAddress());
        //     $this->oSheet->setCellValue("R{$row}", $item->getConjointPhone());
        //     $this->oSheet->setCellValue("R{$row}", $item->getConjointJob());
        //     $this->oSheet->setCellValue("T{$row}", $item->getFatherFirstName() . ' ' . $item->getFatherLastName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getFatherAddress());
        //     $this->oSheet->setCellValue("R{$row}", $item->getFatherPhone());
        //     $this->oSheet->setCellValue("R{$row}", $item->getFatherJob());
        //     $this->oSheet->setCellValue("T{$row}", $item->getMotherFirstName() . ' ' . $item->getMotherLastName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getMotherAddress());
        //     $this->oSheet->setCellValue("R{$row}", $item->getMotherPhone());
        //     $this->oSheet->setCellValue("R{$row}", $item->getMotherJob());
        //     $this->oSheet->setCellValue("T{$row}", $item->getTuteurFirstName() . ' ' . $item->getTuteurLastName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getTuteurAddress());
        //     $this->oSheet->setCellValue("R{$row}", $item->getTuteurPhone());
        //     $this->oSheet->setCellValue("R{$row}", $item->getTuteurJob());
        //     $this->oSheet->setCellValue("R{$row}", $item->getReligiousProfil());
        //     $this->oSheet->setCellValue("R{$row}", $item->getReligiousCongregationName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getReligiousResponsableFoyerName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getReligiousPhone());    
        //     $this->oSheet->setCellValue("R{$row}", $item->getReligiousEmail());    
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccSerie());    
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccAnnee());
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccNumInscription());
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccAutreName());
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccAutreSerie());
        //     $this->oSheet->setCellValue("R{$row}", $item->getBaccMention());
        //     $row++;              
        // }

        foreach($data as $key => $item) {    
            $cellValue = [
                $item->getImmatricule(), 
                $item->getFirstName(),
                $item->getLastName(),                 
                $item->getMention()->getNom(),
                $item->getNiveau()->getLibelle(),
                $item->getParcours() ? $item->getParcours()->getNom(): '',
                $item->getCentre() ? $item->getCentre()->getName() : '',
                $item->getEmail(),
                $item->getDateOfBirth() ? $item->getDateOfBirth()->format('d/m/Y') : '',
                $item->getBirthPlace(),
                $item->getDiplome(),
                $item->getNationality(),
                $item->getCinNum(),
                $item->getCinDeliverDate() ? $item->getCinDeliverDate()->format('d/m/Y'): "",
                $item->getAddress(),
                $item->getPhone1(),
                $item->getConfessionReligieuse(),
                $item->getJob(),
                $item->getPayementRef(),
                $item->getPayementRefDate() ? $item->getPayementRefDate()->format('d/m/Y'): "",
                $item->getConjointFirstName() . ' ' . $item->getConjointLastName(),
                $item->getConjointAddress(),
                $item->getConjointPhone(),
                $item->getConjointJob(),
                $item->getFatherFirstName() . ' ' . $item->getFatherLastName(),
                $item->getFatherAddress(),
                $item->getFatherPhone(),
                $item->getFatherJob(),
                $item->getMotherFirstName() . ' ' . $item->getMotherLastName(),
                $item->getMotherAddress(),
                $item->getMotherPhone(),
                $item->getMotherJob(),
                $item->getTuteurFirstName() . ' ' . $item->getTuteurLastName(),
                $item->getTuteurAddress(),
                $item->getTuteurPhone(),
                $item->getTuteurJob(),
                $item->getReligiousProfil(),
                $item->getReligiousCongregationName(),
                $item->getReligiousResponsableFoyerName(),
                $item->getReligiousPhone(),
                $item->getReligiousEmail(),
                $item->getBaccSerie(),
                $item->getBaccAnnee(),
                $item->getBaccNumInscription(),
                $item->getBaccAutreName(),
                $item->getBaccAutreSerie(),
                $item->getBaccMention()
            ];
            $this->writeCell($cellValue, $row);
            $row++;
        }        
        $this->writeFile();
        return $this->oFile;
    }

    /**
     * @param mixed $data
     * @param Calendar payment $period
     * @param ParameterBagInterface $parameter
     */
    public function getResultat($data, $options, $parameter){
        $anneeUniversitaire = $options['anneeUniversitaire'];
        $mention = $options['mention'];
        $concours  = $options['concours'];
        $parcours = "";
        $racineDirectory     = $parameter->get('concours_directory');
        $filesystem = new Filesystem();
        $exportFileDir = $racineDirectory . '/resultat';
        if(!is_dir($exportFileDir)){
            try {
                $filesystem->mkdir($exportFileDir);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $this->filepath = $exportFileDir;
        $this->filename = $concours->getLibelle(). ".xlsx";
        $row = 1;
        $this->oSheet->setCellValue("A{$row}", 'Liste des candidatures admis au ' . $concours->getLibelle());
        $this->oSpreadsheet->getActiveSheet()->mergeCells("A{$row}:Z{$row}");
        $row++;

        $this->head = ['Matricule', 'Nom', 'Prénom', 'Note', 'Centre', 'Réligion'];
        $this->writeHead($row);
        $cellValue = [];
        $row++;

        foreach($data as $key => $item) {    
            $cellValue = [
                $item['immatricule'],
                $item['last_name'],
                $item['first_name'],
                $item['average'],
                $item['centre'],
                $item['confession_religieuse']
            ];
            $this->writeCell($cellValue, $row);
            $row++;
        }        
        $this->writeFile();
        return $this->oFile;
    }

    private function getStatusLibelle(Int $_statusNum): string {
        switch($_statusNum){
            case ConcoursCandidature::STATUS_CREATED:
                return 'Créées';
                break;
            case ConcoursCandidature::STATUS_APPROVED:
                return 'Approuvées';
                break;
            case ConcoursCandidature::STATUS_DISAPPROVED:
                return 'Refusées';
                break;
        }

        return "";
    }
}