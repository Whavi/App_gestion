<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Form\edit\EditFormDepartementType;
use App\Form\search\SearchTypeDepartement;
use App\Form\addItem\UserFormDepartementType;
use App\Model\SearchDataDepartement;
use App\Repository\DepartementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;

class DepartementController extends AbstractController
{

############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion/departement', name: 'user_gestion_departement')]
#[IsGranted('ROLE_USER')]
public function gestionDepartement(LoggerInterface $logger, DepartementRepository $departementRepository,PersistenceManagerRegistry $doctrine, Request $request, PaginatorInterface $paginatorInterface, ) {
    $users = $departementRepository->findAllOrderedByDepartementRank();
    $this->processDepartementAccueil($request,$doctrine ,$logger); //LOG
    $posts = $paginatorInterface->paginate($users, $request->query->getInt('page', 1), 12);
    $searchDataDepartement = new SearchDataDepartement();
    $form = $this->createForm(SearchTypeDepartement::class, $searchDataDepartement);
    $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $departementRepository->findAllOrderedByNameDepartement($searchDataDepartement);
            $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
            $this->processDepartementRecherche($searchDataDepartement,$doctrine,$logger); //LOG
            return $this->render('pages/user/departement.html.twig', [
                'form' => $form->createView(),
                'departements' => $posts,
                ],
            );
        }
    return $this->render('pages/user/departement.html.twig', [
        'form' => $form->createView(),
        'departements' => $posts,
        ]);
}

############################################################################################################################
##################################################   PAGE DE SUPPRESSION   #################################################
############################################################################################################################
#[Route('/gestion/departement/delete/{id}', name: 'user_gestion_departement_delete', methods: ['GET', 'DELETE'])]
#[IsGranted('ROLE_ADMIN')]
public function gestionDepartementDelete($id,LoggerInterface $logger , DepartementRepository $departementRepository, EntityManagerInterface $manager,  PersistenceManagerRegistry $doctrine) : Response {
    $departement = $departementRepository->find($id);
    if ($departement === null) { return $this->redirectToRoute('user_gestion_departement');}
    $this->processDepartementDelete($departement, $manager,$doctrine, $logger); //LOG
    return $this->redirectToRoute('user_gestion_departement');
}


############################################################################################################################
####################################################   PAGE D'ÉDITION   ####################################################
############################################################################################################################

#[Route('/gestion/departement/edit/{id}', name: 'user_gestion_departement_edit')]
#[IsGranted('ROLE_ADMIN')]
public function gestionDepartementEdit($id,LoggerInterface $logger, DepartementRepository $departementRepository, Request $request,EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
    $departement = $departementRepository->find($id);
    $form = $this->createForm(EditFormDepartementType::class, $departement);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processDepartementEdit($departement, $form->getData(), $manager, $doctrine, $logger); //LOG
        return $this->redirectToRoute('user_gestion_departement');
    }
    $this->processDeparementEditEntry($doctrine, $logger);
    return $this->render('pages/user/edit/editUser.html.twig', [
        'departement' => $departement,
        'form' => $form->createView()
    ]);       
}

############################################################################################################################
####################################################   PAGE D'AJOUT   ######################################################
############################################################################################################################

#[Route('/gestion/departement/addDepartement', name: 'user_gestion_newItemDepartement')]
#[IsGranted('ROLE_ADMIN')]
public function addItemDepartement(LoggerInterface $logger,EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine, Request $request): Response{
    $form = $this->createForm(UserFormDepartementType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->processDepartementCreation($form->getData(), $manager, $doctrine, $logger); //LOG
        return $this->redirectToRoute('user_gestion_departement');
    }
    $this->processDeparementCreationEntry($doctrine, $logger); //LOG
    return $this->render('pages/user/newItem/Departement.html.twig', [
        'form' => $form->createView()
    ]);
}














############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################



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

private function processDepartementAccueil($request,$doctrine ,$logger){   
    $page = $request->query->getInt('page', 1);
    $this->logToDatabase("{user} est rentré dans la page $page d'accueil du département", [
        'user' => $this->getUser(),
    ], "DÉPARTEMENT",$doctrine);

    $logger->info("{user} est rentré dans la page $page d'accueil du département | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}   

private function processDepartementRecherche( $searchDataDepartement,$doctrine,$logger){
    $this->logToDatabase("{user} fait une recherche dans la page département | recherche => {rech}", [
        'user' => $this->getUser(),
        'rech' => $searchDataDepartement->getRecherche(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} fait une recherche dans la page département | recherche => {rech} | heure => {date}", [
        'user' => $this->getUser(),
        'rech' => $searchDataDepartement->getRecherche(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processDepartementDelete($departement, $manager, $doctrine, $logger){
    $this->logToDatabase("{user} a supprimé le département {dep} qui a pour ID => {id}", [
        'id' => $departement->getId(),
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} a supprimé le département {dep} | heure de suppression => {date}", [
        'id' => $departement->getId(),
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);

    $manager = $doctrine->getManager();
    $manager->remove($departement);
    $manager->flush();

    $this->addFlash('success', "Le département a été supprimé");
}

private function processDepartementEdit($departement, $data, $manager, $doctrine, $logger){
    $this->addFlash('success', 'Votre département a bien été modifié.');
    $manager = $doctrine->getManager();
    $manager->persist($data);
    $manager->flush();

    $this->logToDatabase("{user} a modifié le département => {dep}", [
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} a modifié le département => {dep} | heure de changement : {date}", [
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
private function processDepartementCreation($data, $manager,$doctrine, $logger)
{
    $this->addFlash('success','Votre département a bien été créé.');

    $departement = new Departement();
    $departement->setNom($data->getNom());
    $departement->setCreateAt(new \DateTime());
    $departement->setUpdateAt(new \DateTime());

    $manager = $doctrine->getManager();
    $manager->persist($departement);
    $manager->flush();

    $this->logToDatabase("{user} a créé un département => {dep}", [
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} a créé un département => {dep} | heure de création : {date}", [
        'user' => $this->getUser(),
        'dep' => $departement->getNom(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
Private function processDeparementCreationEntry($doctrine, $logger){
    $this->logToDatabase("{user} est rentré dans la page d'ajout de département", [
        'user' => $this->getUser(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} est rentré dans la page d'ajout de département | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

Private function processDeparementEditEntry($doctrine, $logger){
    $this->logToDatabase("{user} est rentré dans la page d'édition de département", [
        'user' => $this->getUser(),
    ], "DÉPARTEMENT", $doctrine);

    $logger->info("{user} est rentré dans la page d'édition de département | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

}