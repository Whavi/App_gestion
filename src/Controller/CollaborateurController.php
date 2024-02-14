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
    #[Route('/gestion/compte/collaborateur', name: 'user_gestion_collaborateur')]
    #[IsGranted('ROLE_USER')]
    public function gestion_collaborateur(LoggerInterface $logger, CollaborateurRepository $CollaborateurRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $currentDateTime = new \DateTime();
        $data = $CollaborateurRepository->findAllOrderedByCollaborateurNumber();
        $posts = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );

        $logger->info("{user} est rentré dans la page d'accueil Collaborateur | heure => {date}", 
        [
        'user'=>$this->getUser(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);

        $searchDataCollaborateur = new SearchDataCollaborateur();
        $form = $this->createForm(SearchTypeCollaborateur::class, $searchDataCollaborateur);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $CollaborateurRepository->findAllOrderedByNameCollaborateur($searchDataCollaborateur);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    12);
                
                $logger->info("{user} fait une recherche dans la page Collaborateur | recherche => {rech} | heure => {date}", 
                    [
                    'user'=>$this->getUser(),
                    'rech'=>$searchDataCollaborateur->getRecherche(),
                    'date'=>$currentDateTime->format('d/m/Y H:i:s'),
                ]);
                
                return $this->render('pages/user/collaborateur.html.twig', [ 
                    'form' => $form->createView(),
                    'collaborateurs' => $posts,]);
                }

        return $this->render('pages/user/collaborateur.html.twig', [
            'form' => $form->createView(),
            'collaborateurs' => $posts,
        ],
        );

    }
    
    
     #[Route('/gestion/compte/collaborateur/delete/{id}', name: 'user_gestion_collaborateur_delete')]
     #[IsGranted('ROLE_ADMIN')]
    public function gestionCollaborateurDelete($id, LoggerInterface $logger, CollaborateurRepository $collaborateurRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $collaborateur = $collaborateurRepository->find($id);
        $currentDateTime = new \DateTime();
        $logger->info("{user} a supprimer l'id : {id} | Collaborateur => {collab} | Email => {mail} | Affectation => {caffecat} | Département => {dep} | heure de suppréssion => {date}", 
        ['id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
        'mail'=>$collaborateur->getEmail(),
        'affec'=>$collaborateur->getAttributions(),
        'dep'=>$collaborateur->getDepartement(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);

        if ($collaborateur === null) {
            return $this->redirectToRoute('user_gestion_collaborateur');
            }
        $this->addFlash('success','Le collaborateur a été supprimer');
        $manager = $doctrine->getManager();
        $manager->remove($collaborateur);
        $manager->flush();
    
        return $this->redirectToRoute('user_gestion_collaborateur');
    }

    #[Route('/gestion/compte/collaborateur/edit/{id}', name: 'user_gestion_collaborateur_edit')]
    #[IsGranted('ROLE_USER')]
    public function gestionCollaborateurEdit($id, LoggerInterface $logger, CollaborateurRepository $collaborateurRepository, Request $request, EntityManagerInterface $manager) : Response {
       $collaborateur = $collaborateurRepository->find($id);
       $currentDateTime = new \DateTime();
       $logger->info("{user} est rentré dans la page d'édition du Collaborateur {col} | heure => {date}", 
       [
       'user'=>$this->getUser(),
       'col'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
       'date'=>$currentDateTime->format('d/m/Y H:i:s'),
   ]);

        $form = $this->createForm(EditFormCollaborateurType::class, $collaborateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $collab = $form->getData();

            $this->addFlash(
                'success',
                'Votre collaborateur a bien été modifier.'
            );

            $manager->persist($collab);
            $manager->flush();

            $logger->info("{user} à modifier le Collaborateur => {collab} | email => {mail} | affectation => {affec} | Département => {dep} | heure de changement : {date}", 
                [
                'user'=>$this->getUser(),
                'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
                'mail'=>$collaborateur->getEmail(),
                'affec'=>$collaborateur->getAttributions(),
                'dep'=>$collaborateur->getDepartement(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);

            return $this->redirectToRoute('user_gestion_collaborateur');


        }
       return $this->render('pages/user/edit/editCollaborateur.html.twig', [
            'utilisateur' => $collaborateur,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/compte/collaborateur/addItem', name: 'user_gestion_newItemCollaborateur')]
    #[IsGranted('ROLE_USER')]
    public function addItemCollaborateur(LoggerInterface $logger, EntityManagerInterface $manager, Request $request) : Response {
        $currentDateTime = new \DateTime();
        $form = $this->createForm(UserFormCollaborateurType::class);
        $form->handleRequest($request);

        $logger->info("{user} est rentré dans la page d'ajout de Collaborateur | heure => {date}", 
        [
        'user'=>$this->getUser(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
        $departement = new Departement();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $collaborateur = new Collaborateur();
            $collaborateur->setNom($data->getNom());
            $collaborateur->setPrenom($data->getPrenom());
            $collaborateur->setEmail($data->getEmail());
            $collaborateur->setDepartement($data->getDepartement($departement));

            $this->addFlash(
                'success',
                'Votre collaborateur a bien été crée.'
            );

            $logger->info("{user} a crée un collaborateur => {collab} | email => {mail} | numéro de commande => Aucune affectation | département => {dep} | heure de création : {date}", 
            [
            'user'=>$this->getUser(),
            'collab'=>strtoupper($collaborateur->getNom()). " ".$collaborateur->getPrenom(),
            'mail'=>$collaborateur->getEmail(),
            'affec'=>$collaborateur->getAttributions(),
            'dep'=>$collaborateur->getDepartement(),
            'date'=>$currentDateTime->format('d/m/Y H:i:s'),
        ]);
    
            $manager->persist($collaborateur);
            $manager->flush();
            return $this->redirectToRoute('user_gestion_collaborateur');
    }
    return $this->render('pages/user/newItem/Collaborateur.html.twig', [
        'form' => $form->createView()
    ]);
    }

}
