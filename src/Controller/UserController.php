<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\edit\EditFormUserType;
use App\Form\search\SearchTypeUser;
use App\Form\addItem\UserFormItemType;
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
use Psr\Log\LoggerInterface;



class UserController extends AbstractController
{
############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion/compte/utilisateur', name: 'user_gestion_utilisateur')]
#[IsGranted('ROLE_USER')]
public function gestion_cpt_utilisateur(LoggerInterface $logger, UserRepository $UserRepository, Request $request, PaginatorInterface $paginatorInterface) {
    $users = $UserRepository->findAllOrderedByRank();
    $posts = $paginatorInterface->paginate($users, $request->query->getInt('page', 1), 12);
    $searchDataUser = new SearchDataUser();
    $form = $this->createForm(SearchTypeUser::class, $searchDataUser);
    $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $UserRepository->findAllOrderedByNameUser($searchDataUser);
            $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
            $this->processUserRecherche($logger, $searchDataUser);
            return $this->render('pages/user/utilisateur.html.twig', [
                'form' => $form->createView(),
                'users' => $posts, 
                ],
            );
        }
    $this->processUserAccueil($request, $logger);
    return $this->render('pages/user/utilisateur.html.twig', [
        'form' => $form->createView(),
        'users' => $posts,
    ]);
}

############################################################################################################################
##################################################   PAGE DE SUPPRESSION   #################################################
############################################################################################################################
#[Route('/gestion/compte/utilisateur/delete/{id}', name: 'user_gestion_utilisateur_delete', methods: ['GET', 'DELETE'])]
#[IsGranted('ROLE_ADMIN')]
public function gestionUserDelete($id,LoggerInterface $logger,  UserRepository $userRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
    $user = $userRepository->find($id);
    if ($user === null) {return $this->redirectToRoute('user_gestion_utilisateur');}
    $this->processUserDelete($user, $id, $manager, $doctrine, $logger); //LOG
    return $this->redirectToRoute('user_gestion_utilisateur');
}

############################################################################################################################
####################################################   PAGE D'ÉDITION   ####################################################
############################################################################################################################
#[Route('/gestion/compte/utilisateur/edit/{id}', name: 'user_gestion_utilisateur_edit')]
#[IsGranted('ROLE_ADMIN')]
public function gestionUserEdit($id,LoggerInterface $logger, UserRepository $userRepository, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher) : Response {
    $utilisateur = $userRepository->find($id);
    $form = $this->createForm(EditFormUserType::class, $utilisateur);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processUserEdit($utilisateur,$form->getData(),$manager,$userPasswordHasher->hashPassword( $utilisateur, $form->get('password')->getData()),$logger);
        return $this->redirectToRoute('user_gestion_utilisateur');
    }
    $this->processUserEditEntry($utilisateur, $logger);
    return $this->render('pages/user/edit/editUser.html.twig', [
        'utilisateur' => $utilisateur,
        'form' => $form->createView()
    ]);
}

############################################################################################################################
####################################################   PAGE D'AJOUT   ######################################################
############################################################################################################################
#[Route('/gestion/compte/utilisateur/addUser', name: 'user_gestion_newItemUser')]
#[IsGranted('ROLE_ADMIN')]
public function addItemUser(LoggerInterface $logger, EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
{
    $form = $this->createForm(UserFormItemType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $userPasswordHashed = $userPasswordHasher->hashPassword($form->getData(), $form->get('password')->getData());
        $this->processUserCreate($form->getData(), $manager, $userPasswordHashed, $logger);

        $this->addFlash('success', 'Votre compte a bien été créé.');
        return $this->redirectToRoute('user_gestion_utilisateur');
    }
    $this->processUserCreateEntry($logger);
    return $this->render('pages/user/newItem/User.html.twig', [
        'form' => $form->createView()
    ]);
}










############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################

private function processUserAccueil($request, $logger){
    $page = $request->query->getInt('page', 1);
    $logger->info("{user} est rentré dans la page $page d'accueil Utilisateur | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
} 


private function processUserRecherche($logger, $searchDataUser){
    $logger->info("{user} fait une recherche dans la page Utilisateur | recherche => {rech} | heure => {date}", [
        'user' => $this->getUser(),
        'rech' => $searchDataUser->getRecherche(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processUserDelete($user, $id, $manager, $doctrine, $logger){
    $this->addFlash('success',"L'utilisateur a été supprimer");
    $manager = $doctrine->getManager();
    $manager->remove($user);
    $manager->flush();
    $logger->info("{user} a supprimer l'utilisateur {utilisateur} | Email => {mail} | id => {id} | heure de suppréssion => {date}", [
        'id'=> $id,
        'user'=>$this->getUser(),
        'utilisateur'=>strtoupper($user->getNom()). " ".$user->getPrenom(),
        'mail'=>$user->getEmail(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
        ]);

    }

private function processUserEdit($user, $data, $manager,$userPasswordHashed, $logger){
    $this->addFlash('success', 'Votre Utilisateur a bien été modifié.');

    $user->setPassword($userPasswordHashed);
    $manager->persist($data);
    $manager->flush();

    $logger->info("{user} à modifier l'utilisateur : {utilisateur} | email => {mail} | heure de changement : {date}", [
        'user'=>$this->getUser(),
        'utilisateur'=>strtoupper($user->getNom()). " ".$user->getPrenom(),
        'mail'=>$user->getEmail(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
        ]);
    }

private function processUserCreate($data, $manager, $userPasswordHashed, $logger)
{
    $userItem = new User();
    $userItem->setNom($data->getNom());
    $userItem->setPrenom($data->getPrenom());
    $userItem->setRoles($data->getRoles());
    $userItem->setEmail($data->getEmail());
    $userItem->setPassword($userPasswordHashed);
    $manager->persist($userItem);
    $manager->flush();

    $logger->info("{user} a créé un Utilisateur => {utilisateur} | email => {mail} | rôles => {roles} | heure de création : {date}", [
        'user' => $this->getUser(),
        'utilisateur' => strtoupper($userItem->getNom()) . " " . $userItem->getPrenom(),
        'mail' => $userItem->getEmail(),
        'roles' => implode(', ', $userItem->getRoles()),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
private function processUserEditEntry($user,$logger){
    $logger->info("{user} est rentré dans la page d'édition de l'Utilisateur {utilisateur} | email => {mail} | heure => {date}", [
        'user'=>$this->getUser(),
        'utilisateur'=>strtoupper($user->getNom()). " ".$user->getPrenom(),
        'mail'=>$user->getEmail(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
   ]);
}

private function processUserCreateEntry($logger){
    $logger->info("{user} est rentré dans la page d'ajout de l'Utilisateur | heure => {date}", [
        'user'=>$this->getUser(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
   ]);
}
    
}