<?php

namespace App\Controller;

use App\Form\SearchTypeAttributionType;
use App\Repository\AttributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Model\SearchDataAttribution;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AttributionController extends AbstractController
{
    #[Route('/gestion/attribution', name: 'user_gestion_attribution')]
    public function gestionAttribution( AttributionRepository $attributionRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $attribution = $attributionRepository->findAllOrderedByAttributionDateTime();

        $posts = $paginatorInterface->paginate(
            $attribution,
            $request->query->getInt('page', 1),
            6
        );

    //     $searchDataAttribution = new SearchDataAttribution();
    //     $form = $this->createForm(SearchTypeAttributionType::class, $searchDataAttribution);

    //     $form->handleRequest($request);
    //         if($form->isSubmitted() && $form->isValid()){
    //             $data = $attributionRepository->findAllOrderedByNameDepartement($searchDataAttribution);
            
    //             $posts = $paginatorInterface->paginate(
    //                 $data,
    //                 $request->query->getInt('page', 1),
    //                 6);


    //     return $this->render('pages/user/departement.html.twig', [
    //         'form' => $form->createView(),
    //         'attributions' => $posts,
    //     ],
    //     );
    // }
    return $this->render('pages/user/attribution.html.twig', [
        // 'form' => $form->createView(),
        'attributions' => $posts,
    ]);
}
    

    #[Route('/gestion/attribution/delete/{id}', name: 'user_gestion_attribution_delete', methods: ['GET', 'DELETE'])]
    public function gestionAttributionDelete($id, AttributionRepository $attributionRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $attribution = $attributionRepository->find($id);
        if ($attribution === null) {
            return $this->redirectToRoute('user_gestion_attribution');
            }

        $this->addFlash('success',"Le département a été supprimer");
        $manager = $doctrine->getManager();
        $manager->remove($attribution);
        $manager->flush();
        return $this->redirectToRoute('user_gestion_attribution');
    }


    #[Route('/gestion/attribution/edit/{id}', name: 'user_gestion_attribution_edit')]
    public function gestionAttributionEdit($id,AttributionRepository $attributionRepository, Request $request, EntityManagerInterface $manager) : Response {
        $attribution = $attributionRepository->find($id);

        $form = $this->createForm(EditFormDepartementType::class, $attribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $this->addFlash(
                'success',
                'Votre compte a bien été modifier.'
            );

            $manager->persist($data);
            $manager->flush();
            return $this->redirectToRoute('user_gestion_attribution');


        }
       return $this->render('pages/user/edit/editAttribution.html.twig', [
            'attributions' => $attribution,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/attribution/addAttribution', name: 'user_gestion_newItemAttribution')]
    public function addItemAttribution(EntityManagerInterface $em, Request $request) : Response {
        
        // $form = $this->createForm(UserFormDepartementType::class);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $data = $form->getData();
        //     $departement = new Departement();
        //     $departement->setNom($data->getNom());
        //     $departement->setCreateAt(new \DateTime());
        //     $departement->setUpdateAt(new \DateTime());
        //     $em->persist($departement);
        //     $em->flush();
            return $this->redirectToRoute('user_gestion_attribution');
    // }
    // return $this->render('pages/user/newItem/Departement.html.twig', [
    //     'form' => $form->createView()]);
    }

}
