<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\SearchType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Model\SearchDataProduct;
use App\Repository\CollaborateurRepository;
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
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserController extends AbstractController
{
    #[Route('compte/utilisateur/edition-mot-de-passe/{id}', name: 'user_edit_password', methods: ['GET', 'POST'])]
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
                    $form->getData()['password']
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
        $form = $this->createForm(SearchType::class, $searchDataProduct);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $searchDataProduct->page = $request->query->getInt('page', 1);
            $productRepository = $productRepository->findBySearch($searchDataProduct);

            return $this->render('pages/user/home.html.twig', [ 
                'form' => $form->createView(),
                'listes' => $posts,]);

        }

        return $this->render('pages/user/home.html.twig', [ 
            'form' => $form->createView(),
            'listes' => $posts,]);

    }


    #[Route('/gestion/compte/collaborateur', name: 'user_gestion_collaborateur')]
    public function gestion_collaborateur(CollaborateurRepository $CollaborateurRepository) {

        $collaborateur = $CollaborateurRepository->findAllOrderedByCollaborateurNumber();

        return $this->render('pages/user/collaborateur.html.twig', [
            'collaborateurs' => $collaborateur,
        ],
        );

    }

    #[Route('/gestion/compte/utilisateur', name: 'user_gestion_utilisateur')]
    public function gestion_cpt_utilisateur(UserRepository $UserRepository) {

        $users = $UserRepository->findAllOrderedByUserName();

        return $this->render('pages/user/utilisateur.html.twig', [
            'users' => $users,
        ],
        );

    }


    #[Route('/gestion/addItem', name: 'user_gestion_newItem')]
    public function add_item(){

        return $this->render('pages/user/newItem.html.twig'
        );

    }
}