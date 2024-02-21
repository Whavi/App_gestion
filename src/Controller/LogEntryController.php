<?php

namespace App\Controller;

use App\Form\LogFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\LogEntryRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\LogEntry;    
use Psr\Log\LoggerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;


class LogEntryController extends AbstractController
{
    #[Route('/log/entry', name: 'admin_app_log_entry')]
    #[IsGranted('ROLE_ADMIN')]

    public function index(LogEntryRepository $logEntryRepository, PaginatorInterface $paginatorInterface, PersistenceManagerRegistry $doctrine, Request $request, LoggerInterface $logger): Response
    { 
        $data = $logEntryRepository->findAllOrderedByLogNumber();
        $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 30);

        $form = $this->createForm(LogFilterType::class,null); 
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {         
            // Récupérer les données du formulaire
            $formData = $form->getData();
            $this->processFiltreLog($formData,$doctrine,$logger);
            // Filtrer les résultats en fonction des niveaux et catégories sélectionnés
            $filteredData = $logEntryRepository->filterByLevelsAndCategories($formData->getLevel(),$formData->getChannel(), $formData->getCreatedAt());
            $posts = $paginatorInterface->paginate($filteredData, $request->query->getInt('page', 1), 30);

            return $this->render('pages/user/log_entry/index.html.twig', [
                'log_entries' => $posts,
                'form' => $form->createView(),
            ]);
        }
        $this->processLogsAccueilEntry($doctrine,$request, $logger);
        return $this->render('pages/user/log_entry/index.html.twig', [
            'log_entries' => $posts,
            'form' => $form->createView(),
        ]);
    }


private function logToDatabase(string $message, array $context = [], $channel,  ?PersistenceManagerRegistry $doctrine = null, $level = 1 ): void
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

private function processLogsAccueilEntry($doctrine,$request, $logger){
    $page = $request->query->getInt('page', 1);

    $this->LogToDatabase("{user} est rentré dans la page $page d'accueil des LOGS ", [
        'user'=>$this->getUser(),
    ],"LOG", $doctrine,0);

    $logger->info("{user} est rentré dans la page $page d'accueil des LOGS | heure => {date}", [
        'user'=>$this->getUser(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}


private function processFiltreLog($log,$doctrine,$logger){
    $this->logToDatabase('{user} a appliqué le filtre : Niveau => {level} | Catégorie => {channel} | date du filtre => {date}', [
        'user' => $this->getUser(),
        'date' => $log->getCreatedAt()->format('Y/m/d'),
        'level' => $log->getLevel() ?: 'Aucune valeur',
        'channel' => $log->getChannel() ?: 'Aucune valeur',
    ], 'LOG', $doctrine, 4);

    $logger->info("{user} a appliqué le filtre : Niveau => {level} | Catégorie => {channel} | date du filtre => {dateFiltre} | heure => {date}", [
        'user' => $this->getUser(),
        'dateFiltre' => $log->getCreatedAt()->format('Y/m/d'),
        'level' => $log->getLevel() ?: 'Aucune valeur',
        'channel' => $log->getChannel() ?: 'Aucune valeur',
        'date' => (new \DateTime)->format('d/m/Y H:i:s'),
    ]);
    
}
}
