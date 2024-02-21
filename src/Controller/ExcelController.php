<?php

namespace App\Controller;

use App\Entity\Attribution;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;


class ExcelController extends AbstractController
{
#[Route('/gestion/{currentFunction}/attribution/exportExcel', name: 'user_gestion_attribution_excel')]
#[IsGranted('ROLE_USER')]
public function exportExcel(LoggerInterface $logger, EntityManagerInterface $entityManager, PersistenceManagerRegistry $doctrine, $currentFunction): Response
{
    if ($currentFunction === 'nouvellesAttributions') {
        $data = $entityManager->getRepository(Attribution::class)->findAllOrderedByAttributionId();
    } else {
        $data = $entityManager->getRepository(Attribution::class)->findOldAttributions();
    }
    usort($data, function($a, $b) {
        return strcmp(strtoupper($a->getCollaborateur()->getNom()), strtoupper($b->getCollaborateur()->getNom()));
    });
    
    // Créer une instance de PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Ajouter les en-têtes au fichier Excel
    $sheet->setCellValue('A1', 'Salarié');
    $sheet->setCellValue('B1', "Date d'attribution");
    $sheet->setCellValue('C1', "Type d'appareil");
    $sheet->setCellValue('D1', "Marque d'Ordinateur");
    $sheet->setCellValue('E1', "Num. Série");
    $sheet->setCellValue('F1', "Réf. log");
    $sheet->setCellValue('G1', "Nom de l'appareil");
    $sheet->setCellValue('H1', "Adresse e-mail");
    $sheet->setCellValue('I1', "Département");
    $sheet->setCellValue('J1', "Autre matériel");
    $sheet->setCellValue('K1', "Remarques");
    $sheet->setCellValue('L1', "Date de Restitution");
    // Appliquer des styles aux en-têtes
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], // Texte en gras, couleur blanc
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0060CC']], // Fond bleu
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ];
    
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
    // Ajouter les données au fichier Excel
    $row = 2; // Commencer à la ligne 2 après les en-têtes
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, strtoupper($item->getCollaborateur()->getNom()) . ' ' . $item->getCollaborateur()->getPrenom());
        $sheet->setCellValue('B' . $row, $item->getDateAttribution()->format('d/m/Y')); 
        $sheet->setCellValue('C' . $row, $item->getProduct()->getCategory());
        $sheet->setCellValue('D' . $row, $item->getProduct()->getNom());
        $sheet->setCellValue('E' . $row, $item->getProduct()->getIdentifiant());
        $sheet->setCellValue('F' . $row, $item->getProduct()->getRef());
        $sheet->setCellValue('G' . $row, $item->getProduct()->getRef());
        $sheet->setCellValue('H' . $row, $item->getCollaborateur()->getEmail());
        $sheet->setCellValue('I' . $row, $item->getCollaborateur()->getDepartement());           
        $sheet->setCellValue('J' . $row, $item->getDescriptionProduct());
        $sheet->setCellValue('K' . $row, $item->getRemarque());
        $sheet->setCellValue('L' . $row, $item->getDateRestitution()->format('d/m/Y')); 
        // Appliquer un style aux cellules de données
        $dataStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN], // Trait moins épais uniquement sur les côtés horizontaux
                'vertical' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM], // Trait plus épais uniquement sur les côtés verticaux
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($dataStyle);
        // Ajouter d'autres champs selon votre modèle de données
        $row++;
    }
    // Ajuster la largeur des colonnes en fonction du contenu
    foreach (range('A', 'L') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    // Enregistrer le fichier Excel
    $writer = new Xlsx($spreadsheet);
    $excelFileName = 'Inventaire_data.xlsx';
    $writer->save($excelFileName);
    $this->processExcelLog($currentFunction,$doctrine, $logger);
    // Retourner le fichier Excel en réponse
    return $this->file($excelFileName, 'Inventaire.xlsx');
}



############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################

private function logToDatabase(string $message, string $channel, ?PersistenceManagerRegistry $doctrine = null, array $context = [], int $level = 1): void
{
    // Merge context parameters into the message
    foreach ($context as $key => $value) {
        $message = str_replace("{{$key}}", $value, $message);
    }

    $logEntry = new LogEntry();
    $logEntry->setMessage($message);
    $logEntry->setCreatedAt(new \DateTime());
    $logEntry->setChannel($channel);
    $logEntry->setLevel($level);

    $entityManager = $doctrine->getManager();
    $entityManager->persist($logEntry);
    $entityManager->flush();
}


private function processExcelLog($currentFunction, $doctrine, $logger){
    $this->logToDatabase("{user} a exporté les données vers un fichier Excel pour {function}","ATTRIBUTION", $doctrine ,[
        'user' => $this->getUser(),
        'function' => $currentFunction,
    ],1);
    $logger->info("{user} a exporté les données vers un fichier Excel pour {function} le {date}", [
        'user' => $this->getUser(),
        'function' => $currentFunction,
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

}




