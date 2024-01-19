<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditFormUserType;
use App\Form\SearchTypeUser;
use App\Form\UserFormItemType;
use App\Model\SearchDataUser;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserController extends AbstractController
{
    #[Route('/gestion/compte/utilisateur', name: 'user_gestion_utilisateur')]
    #[IsGranted('ROLE_USER')]
    public function gestion_cpt_utilisateur(UserRepository $UserRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $users = $UserRepository->findAllOrderedByRank();

        $posts = $paginatorInterface->paginate(
            $users,
            $request->query->getInt('page', 1),
            12
        );

        $searchDataUser = new SearchDataUser();
        $form = $this->createForm(SearchTypeUser::class, $searchDataUser);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $UserRepository->findAllOrderedByNameUser($searchDataUser);
            
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    12);


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
    #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_USER')]
    public function gestionUserEdit($id, UserRepository $userRepository, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher) : Response {
       $utilisateur = $userRepository->find($id);

        $form = $this->createForm(EditFormUserType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $user->setPlainpassword($user->getPassword());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                ));

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
    #[IsGranted('ROLE_USER')]
    public function addItemUser(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $userPasswordHasher) : Response {
        
        $form = $this->createForm(UserFormItemType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userItem = new User();
            $userItem->setNom($data->getNom());
            $userItem->setPrenom($data->getPrenom());
            $userItem->setRoles($data->getRoles());
            $userItem->setEmail($data->getEmail());
            $userItem->setPlainpassword($data->getPassword());
            $userItem->setPassword(
                $userPasswordHasher->hashPassword(
                    $data,
                    $form->get('password')->getData()
                ));

                $this->addFlash(
                    'success',
                    'Votre compte a bien été crée.'
                );
            $em->persist($userItem);
            $em->flush();
            return $this->redirectToRoute('user_gestion_utilisateur');

    }
    return $this->render('pages/user/newItem/User.html.twig', [
        'form' => $form->createView()
    ]);
    }





    // Test de changement de mot de passe


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