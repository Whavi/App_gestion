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

class DepartementController extends AbstractController
{
    #[Route('/gestion/departement', name: 'user_gestion_departement')]
    #[IsGranted('ROLE_USER')]
    public function gestionDepartement(LoggerInterface $logger, DepartementRepository $departementRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $currentDateTime = new \DateTime();
        $users = $departementRepository->findAllOrderedByDepartementRank();
        
        $logger->info("{user} est rentré dans la page d'accueil du département | heure => {date}", 
        [
        'user'=>$this->getUser(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
        $posts = $paginatorInterface->paginate(
            $users,
            $request->query->getInt('page', 1),
            12
        );

        $searchDataDepartement = new SearchDataDepartement();
        $form = $this->createForm(SearchTypeDepartement::class, $searchDataDepartement);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $departementRepository->findAllOrderedByNameDepartement($searchDataDepartement);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    12);

                $logger->info("{user} fait une recherche dans la page département | recherche => {rech} | heure => {date}", 
                [
                'user'=>$this->getUser(),
                'rech'=>$searchDataDepartement->getRecherche(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
                ]);


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
    
    #[Route('/gestion/departement/delete/{id}', name: 'user_gestion_departement_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function gestionDepartementDelete($id,LoggerInterface $logger,  DepartementRepository $departementRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $departement = $departementRepository->find($id);
        $currentDateTime = new \DateTime();
        if ($departement === null) {
            return $this->redirectToRoute('user_gestion_departement');
            }
        $this->addFlash('success',"Le département a été supprimer");
        $manager = $doctrine->getManager();
        $manager->remove($departement);
        $manager->flush();
        $logger->info("{user} a supprimer le département {dep} | heure de suppréssion => {date}", 
        ['id'=> $id,
        'user'=>$this->getUser(),
        'dep'=>$departement->getNom(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
    
        return $this->redirectToRoute('user_gestion_departement');
    }


    #[Route('/gestion/departement/edit/{id}', name: 'user_gestion_departement_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function gestionDepartementEdit($id,LoggerInterface $logger,  DepartementRepository $departementRepository, Request $request, EntityManagerInterface $manager) : Response {
       $departement = $departementRepository->find($id);
       $currentDateTime = new \DateTime();
       $logger->info("{user} est rentré dans la page d'édition du département {dep} | heure => {date}", 
       [
       'user'=>$this->getUser(),
       'dep'=>$departement->getNom(),
       'date'=>$currentDateTime->format('d/m/Y H:i:s'),
   ]);

        $form = $this->createForm(EditFormDepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $this->addFlash(
                'success',
                'Votre département a bien été modifier.'
            );

            $manager->persist($data);
            $manager->flush();

            $logger->info("{user} à modifier le département => {dep} | heure de changement : {date}", 
                [
                'user'=>$this->getUser(),
                'dep'=>$departement->getNom(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);

            return $this->redirectToRoute('user_gestion_departement');


        }
       return $this->render('pages/user/edit/editUser.html.twig', [
            'departement' => $departement,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/departement/addDepartement', name: 'user_gestion_newItemDepartement')]
    #[IsGranted('ROLE_ADMIN')]
    public function addItemDepartement(LoggerInterface $logger, EntityManagerInterface $em, Request $request) : Response {
        
        $currentDateTime = new \DateTime();

        $logger->info("{user} est rentré dans la page d'ajout de département | heure => {date}", 
        [
        'user'=>$this->getUser(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);

        $form = $this->createForm(UserFormDepartementType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $departement = new Departement();
            $departement->setNom($data->getNom());
            $departement->setCreateAt(new \DateTime());
            $departement->setUpdateAt(new \DateTime());

            $this->addFlash(
                'success',
                'Votre département a bien été crée.'
            );

            $em->persist($departement);
            $em->flush();

            $logger->info("{user} a crée un département => {dep} | heure de création : {date}", 
            [
            'user'=>$this->getUser(),
            'dep'=> $departement->getNom(),
            'date'=>$currentDateTime->format('d/m/Y H:i:s'),
        ]);

            return $this->redirectToRoute('user_gestion_departement');
    }
    return $this->render('pages/user/newItem/Departement.html.twig', [
        'form' => $form->createView()
    ]);
    }

}
