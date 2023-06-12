<?php

namespace App\Controller;

use App\Entity\Collaborateur;
use App\Entity\Departement;
use App\Entity\User;
use App\Entity\Product;
use App\Form\EditFormCollaborateurType;
use App\Form\EditFormDepartementType;
use App\Form\EditFormProductType;
use App\Form\EditFormUserType;
use App\Form\ProductFormItemType;
use App\Form\SearchTypeCollaborateur;
use App\Form\SearchTypeDepartement;
use App\Form\SearchTypeProduct;
use App\Form\SearchTypeUser;
use App\Form\UserFormDepartementType;
use App\Form\UserFormItemType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Model\SearchDataProduct;
use App\Model\SearchDataCollaborateur;
use App\Model\SearchDataDepartement;
use App\Model\SearchDataUser;
use App\Repository\CollaborateurRepository;
use App\Repository\DepartementRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserController extends AbstractController
{


    #[Route('/gestion', name: 'user_gestion')]
     ##[IsGranted('ROLE_USER')]
     public function gestion(ProductRepository $productRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $data = $productRepository->findAllOrderedByProductIdentifiant();

        $posts = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            6
        );

        $searchDataProduct = new SearchDataProduct();
        $form = $this->createForm(SearchTypeProduct::class, $searchDataProduct);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $productRepository->findAllOrderedByNameProduct($searchDataProduct);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    6);
                
                return $this->render('pages/user/home.html.twig', [ 
                    'form' => $form->createView(),
                    'listes' => $posts,]);
                }
                
        return $this->render('pages/user/home.html.twig', [ 
            'form' => $form->createView(),
            'listes' => $posts,]);
        }
    
    #[Route('/gestion/delete/{id}', name: 'user_gestion_delete')]
    public function gestionProductDelete($id, ProductRepository $productRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $product = $productRepository->find($id);
        if ($product === null) {
            return $this->redirectToRoute('user_gestion');
            }

        $this->addFlash('success','Le produit a été supprimer');
        $manager = $doctrine->getManager();
        $manager->remove($product);
        $manager->flush();
    
        return $this->redirectToRoute('user_gestion');
    }

    #[Route('/gestion/edit/{id}', name: 'user_gestion_edit')]
    public function gestionProductEdit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $manager) : Response {
       $product = $productRepository->find($id);

        $form = $this->createForm(EditFormProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $productdata = $form->getData();
            $productdata->setUpdatedAt(new \DateTime());

            $this->addFlash(
                'success',
                'Votre compte a bien été modifier.'
            );

            $manager->persist($productdata);
            $manager->flush();
            return $this->redirectToRoute('user_gestion');


        }
       return $this->render('pages/user/edit/editProduct.html.twig', [
            'utilisateur' => $product,
            'form' => $form->createView()
              ]);
    }


    #[Route('/gestion/addItem', name: 'user_gestion_newItemProduct')]
    public function add_item(EntityManagerInterface $em, Request $request) : Response {

        $form = $this->createForm(ProductFormItemType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $product = new Product();
            $product->setIdentifiant($data->getIdentifiant());
            $product->setNom($data->getNom());
            $product->setCategory($data->getCategory());
            $product->setUpdatedAt($data->getCreatedAt());

            $em->persist($product);
            $em->flush();


            return $this->redirectToRoute('user_gestion');

    }
    return $this->render('pages/user/newItem/Product.html.twig', [
        'form' => $form->createView()
    ]);
}













    #[Route('/gestion/compte/collaborateur', name: 'user_gestion_collaborateur')]
    public function gestion_collaborateur(CollaborateurRepository $CollaborateurRepository, Request $request, PaginatorInterface $paginatorInterface) {


        // $NameCollborateur = $CollaborateurRepository->findAllOrderedByInnerJoinCollaborateurName();
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
                    // 'Collaborateur_name' => $NameCollborateur,
                    'collaborateurs' => $posts,]);
                }

        return $this->render('pages/user/collaborateur.html.twig', [
            'form' => $form->createView(),
            // 'Collaborateur_name' => $NameCollborateur,
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
                'Votre compte a bien été modifier.'
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

        return $this->render('pages/user/newItem.html.twig');
    }













    


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
                    6);


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
                'Votre compte a bien été modifier.'
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
            $em->persist($departement);
            $em->flush();
            return $this->redirectToRoute('user_gestion_departement');
    }
    return $this->render('pages/user/newItem/Departement.html.twig', [
        'form' => $form->createView()
    ]);
    }



    #[Route('/gestion/compte/utilisateur', name: 'user_gestion_utilisateur')]
    public function gestion_cpt_utilisateur(UserRepository $UserRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $users = $UserRepository->findAllOrderedByRank();

        $posts = $paginatorInterface->paginate(
            $users,
            $request->query->getInt('page', 1),
            6
        );

        $searchDataUser = new SearchDataUser();
        $form = $this->createForm(SearchTypeUser::class, $searchDataUser);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $UserRepository->findAllOrderedByNameUser($searchDataUser);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    6);


        return $this->render('pages/user/utilisateur.html.twig', [
            'form' => $form->createView(),
            'users' => $posts,
        ],
        );

    }
    return $this->render('pages/user/utilisateur.html.twig', [
        'form' => $form->createView(),
        'users' => $posts,
    ]);
}
    
    #[Route('/gestion/compte/utilisateur/delete/{id}', name: 'user_gestion_utilisateur_delete', methods: ['GET', 'DELETE'])]
    public function gestionUserDelete($id, UserRepository $userRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        $user = $userRepository->find($id);
        if ($user === null) {
            return $this->redirectToRoute('user_gestion_utilisateur');
            }
        $this->addFlash('success',"Le'utilisateur a été supprimer");
        $manager = $doctrine->getManager();
        $manager->remove($user);
        $manager->flush();
    
        return $this->redirectToRoute('user_gestion_utilisateur');
    }

    #[Route('/gestion/compte/utilisateur/edit/{id}', name: 'user_gestion_utilisateur_edit')]
    public function gestionUserEdit($id, UserRepository $userRepository, Request $request, EntityManagerInterface $manager) : Response {
       $utilisateur = $userRepository->find($id);

        $form = $this->createForm(EditFormUserType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();

            $this->addFlash(
                'success',
                'Votre compte a bien été modifier.'
            );

            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('user_gestion_utilisateur');


        }
       return $this->render('pages/user/edit/editUser.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView()
              ]);
    }

    #[Route('/gestion/compte/utilisateur/addUser', name: 'user_gestion_newItemUser')]
    public function addItemUser(EntityManagerInterface $em, Request $request) : Response {
        
        $form = $this->createForm(UserFormItemType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userItem = new User();
            $userItem->setNom($data['nom']);
            $userItem->setPrenom($data['prenom']);
            $userItem->setEmail($data['email']);
            $userItem->setPassword($data['password']);
            $userItem->setRoles($data['roles']);
            $em->persist($userItem);
            $em->flush();
            return $this->redirectToRoute('user_gestion_utilisateur');

    }
    return $this->render('pages/user/newItem/User.html.twig', [
        'form' => $form->createView()
    ]);
    }




































    // Test de changement de mot de pase


    // #[Route('compte/utilisateur/edition-mot-de-passe/{id}', name: 'user_edit_password', methods: ['GET', 'POST'])]
    // public function editPassword(
    //     User $choosenUser,
    //     Request $request,
    //     EntityManagerInterface $manager,
    //     UserPasswordHasherInterface $hasher
    // ): Response {
    //     $form = $this->createForm(UserPasswordType::class);

    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         if ($hasher->isPasswordValid($choosenUser, $form->getData()['password'])) {
    //             $choosenUser->setPassword(
    //                 $form->getData()['password']
    //             );

    //             $this->addFlash(
    //                 'success',
    //                 'Le mot de passe a été modifié.'
    //             );

    //             $manager->persist($choosenUser);
    //             $manager->flush();

    //             return $this->redirectToRoute('app_main');
    //         } else {
    //             $this->addFlash(
    //                 'warning',
    //                 'Le mot de passe renseigné est incorrect.'
    //             );
    //         }
    //     }

    //     return $this->render('pages/user/editPassword.html.twig', [
    //         'form' => $form->createView()
    //     ]);
    // }
    // }

    }