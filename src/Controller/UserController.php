<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;


class UserController extends AbstractController
{
    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(
        User $choosenUser,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($choosenUser, $form->getData()['password'])) {
                $choosenUser->setPassword(
                    $form->getData()['newPassword']
                );

                $this->addFlash(
                    'success',
                    'Le mot de passe a été modifié.'
                );

                $manager->persist($choosenUser);
                $manager->flush();

                return $this->redirectToRoute('app_main');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/editPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/gestion', name: 'user_gestion', methods: ['GET', 'POST'])]
    public function gestion(ProductRepository $productRepository) {

        $lists = $productRepository->findAllOrderedByProductName();

        return $this->render('pages/user/home.html.twig', [
            'listes' => $lists,
        ],
        );

    }

    #[Route('/gestion/addItem', name: 'user_gestion_newItem', methods: ['GET', 'POST'])]
    public function add_item(){

        return $this->render('pages/user/newItem.html.twig'
        );

    }



}



