<?php

namespace App\Controller;

use App\Entity\Collaborateur;
use App\Entity\Departement;
use App\Form\edit\EditFormCollaborateurType;
use App\Form\search\SearchTypeCollaborateur;
use App\Form\addItem\UserFormCollaborateurType;
use App\Model\SearchDataCollaborateur;
use App\Repository\CollaborateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

class CollaborateurController extends AbstractController
{
############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion/compte/collaborateur', name: 'user_gestion_collaborateur')]
#[IsGranted('ROLE_USER')]
public function gestion_collaborateur(LoggerInterface $logger, CollaborateurRepository $CollaborateurRepository, Request $request, PaginatorInterface $paginatorInterface) {
    $data = $CollaborateurRepository->findAllOrderedByCollaborateurNumber();
    $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
    $searchDataCollaborateur = new SearchDataCollaborateur();
    $form = $this->createForm(SearchTypeCollaborateur::class, $searchDataCollaborateur);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()){
        $data = $CollaborateurRepository->findAllOrderedByNameCollaborateur($searchDataCollaborateur);
        $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
        $this->processCollaborateurRecherche($logger,$searchDataCollaborateur); //LOG
        return $this->render('pages/user/collaborateur.html.twig', [ 
            'form' => $form->createView(),
            'collaborateurs' => $posts,]);
        }
    $this->processCollaborateurAccueil($logger, $request); //LOG
    return $this->render('pages/user/collaborateur.html.twig', [
        'form' => $form->createView(),
        'collaborateurs' => $posts,
    ],
    );
}
    
############################################################################################################################
##################################################   PAGE DE SUPPRESSION   #################################################
############################################################################################################################
#[Route('/gestion/compte/collaborateur/delete/{id}', name: 'user_gestion_collaborateur_delete')]
#[IsGranted('ROLE_ADMIN')]
public function gestionCollaborateurDelete($id, LoggerInterface $logger, CollaborateurRepository $collaborateurRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
    $collaborateur = $collaborateurRepository->find($id);
    if ($collaborateur === null) {return $this->redirectToRoute('user_gestion_collaborateur');}
    $this->processCollaborateurtDelete($collaborateur, $id, $manager, $doctrine, $logger); //LOG
    return $this->redirectToRoute('user_gestion_collaborateur');
}


############################################################################################################################
####################################################   PAGE D'ÉDITION   ####################################################
############################################################################################################################
#[Route('/gestion/compte/collaborateur/edit/{id}', name: 'user_gestion_collaborateur_edit')]
#[IsGranted('ROLE_USER')]
public function gestionCollaborateurEdit($id, LoggerInterface $logger, CollaborateurRepository $collaborateurRepository, Request $request, EntityManagerInterface $manager) : Response {
    $collaborateur = $collaborateurRepository->find($id);
    $form = $this->createForm(EditFormCollaborateurType::class, $collaborateur);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processCollaborateurEdit($collaborateur, $form->getData(), $manager, $logger); //LOG
        return $this->redirectToRoute('user_gestion_collaborateur');
    }
    $this->processCollaborateurEditEntry($collaborateur, $logger); //LOG
    return $this->render('pages/user/edit/editCollaborateur.html.twig', [
         'utilisateur' => $collaborateur,
         'form' => $form->createView()
           ]);
}
    
############################################################################################################################
####################################################   PAGE D'AJOUT   ######################################################
############################################################################################################################
#[Route('/gestion/compte/collaborateur/addItem', name: 'user_gestion_newItemCollaborateur')]
#[IsGranted('ROLE_USER')]
public function addItemCollaborateur(LoggerInterface $logger, EntityManagerInterface $manager, Request $request) : Response {
    $form = $this->createForm(UserFormCollaborateurType::class);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->processCollaborateurCreation($form->getData(), $manager, $logger); //LOG
        return $this->redirectToRoute('user_gestion_collaborateur');
    }
    $this->processCollaborateurCreationEntry($logger); //LOG
    return $this->render('pages/user/newItem/Collaborateur.html.twig', [
        'form' => $form->createView()
    ]);
}






















############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################

private function processCollaborateurAccueil($logger, $request){   
    $page = $request->query->getInt('page', 1);
    $logger->info("{user} est rentré dans la page $page d'accueil Collaborateur | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processCollaborateurRecherche($logger, $searchDataCollaborateur){
    $logger->info("{user} fait une recherche dans la page Collaborateur | recherche => {rech} | heure => {date}", [
        'user' => $this->getUser(),
        'rech' => $searchDataCollaborateur->getRecherche(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processCollaborateurtDelete($collaborateur, $id, $manager, $doctrine, $logger){
    $this->addFlash('success','Le collaborateur a été supprimer');
    $manager = $doctrine->getManager();
    $manager->remove($collaborateur);
    $manager->flush();

    $logger->info("{user} a supprimer l'id : {id} | Collaborateur => {collab} | Email => {mail} | Affectation => {Countaffec} | Département => {dep} | heure de suppréssion => {date}", [
    'id'=> $id,
    'user'=>$this->getUser(),
    'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
    'mail'=>$collaborateur->getEmail(),
    'Countaffec'=>count($collaborateur->getAttributions()),
    'dep'=>$collaborateur->getDepartement(),
    'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
 private function processCollaborateurEdit($collaborateur, $data, $manager, $logger){
        $this->addFlash('success', 'Votre département a bien été modifié.');
        $manager->persist($data);
        $manager->flush();
    
        $logger->info("{user} à modifier le Collaborateur => {collab} | email => {mail} | Département => {dep} | heure de changement : {date}", [
        'user'=>$this->getUser(),
        'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
        'mail'=>$collaborateur->getEmail(),
        'dep'=>$collaborateur->getDepartement(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
    ]);
    }
private function processCollaborateurEditEntry($collaborateur,$logger){
    $logger->info("{user} est rentré dans la page d'édition du Collaborateur {col} | heure => {date}", [
       'user'=>$this->getUser(),
       'col'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
       'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
   ]);
}

private function processCollaborateurCreationEntry($logger){
    $logger->info("{user} est rentré dans la page d'ajout de Collaborateur | heure => {date}", [
    'user'=>$this->getUser(),
    'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
private function processCollaborateurCreation($data, $manager, $logger){
    $collaborateur = new Collaborateur();
    $collaborateur->setNom($data->getNom());
    $collaborateur->setPrenom($data->getPrenom());
    $collaborateur->setEmail($data->getEmail());
    $collaborateur->setDepartement($data->getDepartement(new Departement()));

    $manager->persist($collaborateur);
    $manager->flush();
    $this->addFlash('success', 'Votre collaborateur a bien été crée.');

    $logger->info("{user} a crée un collaborateur => {collab} | email => {mail} | numéro de commande => Aucune affectation | département => {dep} | heure de création : {date}", [
    'user'=>$this->getUser(),
    'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
    'mail'=>$collaborateur->getEmail(),
    'affec'=>$collaborateur->getAttributions(),
    'dep'=>$collaborateur->getDepartement(),
    'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

}
