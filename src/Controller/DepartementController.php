<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Form\EditFormDepartementType;
use App\Form\SearchTypeDepartement;
use App\Form\UserFormDepartementType;
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

class DepartementController extends AbstractController
{
    #[Route('/gestion/departement', name: 'user_gestion_departement')]
    public function gestionDepartement( DepartementRepository $departementRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $users = $departementRepository->findAllOrderedByDepartementRank();

        $posts = $paginatorInterface->paginate(
            $users,
            $request->query->getInt('page', 1),
            6
        );

        $searchDataDepartement = new SearchDataDepartement();
        $form = $this->createForm(SearchTypeDepartement::class, $searchDataDepartement);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $departementRepository->findAllOrderedByNameDepartement($searchDataDepartement);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    15);


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
    public function gestionDepartementDelete($id, DepartementRepository $departementRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $departement = $departementRepository->find($id);
        if ($departement === null) {
            return $this->redirectToRoute('user_gestion_departement');
            }
        $this->addFlash('success',"Le département a été supprimer");
        $manager = $doctrine->getManager();
        $manager->remove($departement);
        $manager->flush();
    
        return $this->redirectToRoute('user_gestion_departement');
    }


    #[Route('/gestion/departement/edit/{id}', name: 'user_gestion_departement_edit')]
    public function gestionDepartementEdit($id, DepartementRepository $departementRepository, Request $request, EntityManagerInterface $manager) : Response {
       $departement = $departementRepository->find($id);

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
            return $this->redirectToRoute('user_gestion_departement');


        }
       return $this->render('pages/user/edit/editUser.html.twig', [
            'departement' => $departement,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/departement/addDepartement', name: 'user_gestion_newItemDepartement')]
    public function addItemDepartement(EntityManagerInterface $em, Request $request) : Response {
        
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
            return $this->redirectToRoute('user_gestion_departement');
    }
    return $this->render('pages/user/newItem/Departement.html.twig', [
        'form' => $form->createView()
    ]);
    }

}
