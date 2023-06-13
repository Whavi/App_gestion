<?php

namespace App\Controller;

use App\Entity\Collaborateur;
use App\Entity\Departement;
use App\Form\EditFormCollaborateurType;
use App\Form\SearchTypeCollaborateur;
use App\Form\UserFormCollaborateurType;
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

class CollaborateurController extends AbstractController
{
    #[Route('/gestion/compte/collaborateur', name: 'user_gestion_collaborateur')]
    public function gestion_collaborateur(CollaborateurRepository $CollaborateurRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $data = $CollaborateurRepository->findAllOrderedByCollaborateurNumber();
        $posts = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            6
        );


        $searchDataCollaborateur = new SearchDataCollaborateur();
        $form = $this->createForm(SearchTypeCollaborateur::class, $searchDataCollaborateur);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $CollaborateurRepository->findAllOrderedByNameCollaborateur($searchDataCollaborateur);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    6);
                
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
    public function gestionCollaborateurDelete($id, CollaborateurRepository $collaborateurRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $collaborateur = $collaborateurRepository->find($id);
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
    public function gestionCollaborateurEdit($id, CollaborateurRepository $collaborateurRepository, Request $request, EntityManagerInterface $manager) : Response {
       $collaborateur = $collaborateurRepository->find($id);

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
            return $this->redirectToRoute('user_gestion_collaborateur');


        }
       return $this->render('pages/user/edit/editCollaborateur.html.twig', [
            'utilisateur' => $collaborateur,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/compte/collaborateur/addItem', name: 'user_gestion_newItemCollaborateur')]
    public function addItemCollaborateur(EntityManagerInterface $manager, Request $request) : Response {

        $form = $this->createForm(UserFormCollaborateurType::class);
        $form->handleRequest($request);

        $departement = new Departement();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $collaborateur = new Collaborateur();
            $collaborateur->setNom($data->getNom());
            $collaborateur->setPrenom($data->getPrenom());
            $collaborateur->setEmail($data->getEmail());
            $collaborateur->setDepartement($data->getDepartement($departement));
    
            $manager->persist($collaborateur);
            $manager->flush();
            return $this->redirectToRoute('user_gestion_departement');
    }
    return $this->render('pages/user/newItem/Collaborateur.html.twig', [
        'form' => $form->createView()
    ]);
    }

}
