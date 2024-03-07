<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\other\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;




class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'security.login')]
    public function login(AuthenticationUtils $authenticationUtils,PersistenceManagerRegistry $doctrine, LoggerInterface $logger): Response
    {

        if ($this->getUser()) {
            $this->processConnexion($doctrine, $logger);
            return $this->redirectToRoute('user_gestion'); }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('pages/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }



    #[Route('/deconnexion', 'security.logout')]
    public function logout()
    {
        // Nothing to do here..
    }




    #[Route('/inscription', name:'security.registration', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function registration(Request $request,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine, LoggerInterface $logger): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                ));
            $user->setAzureId($user->getEmail());

            $manager->persist($user);
            $manager->flush();

            $this->processInscription($user, $doctrine, $logger);

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }



    private function logToDatabase(string $message, string $channel, ?PersistenceManagerRegistry $doctrine = null, array $context = [], int $level = 1): void
{
    // Merge context parameters into the message
    foreach ($context as $key => $value) {
        $message = str_replace("{{$key}}", $value, $message);
    }

    $logEntry = new LogEntry();
    $logEntry->setMessage($message);
    $logEntry->setCreatedAt(new \DateTime());
    $logEntry->setChannel($channel);
    $logEntry->setLevel($level);

    $entityManager = $doctrine->getManager();
    $entityManager->persist($logEntry);
    $entityManager->flush();
}


private function processInscription($user,$doctrine,$logger){
    $this->logToDatabase("Le compte {user} à été crée | email => {mail} | Nom et prenom => {utilisateur}", "INSCRIPTION",$doctrine,[
        'user'=>$this->getUser(),
        'utilisateur'=>strtoupper($user->getNom()). " ".$user->getPrenom(),
        'mail'=>$user->getEmail(),
   ],1);
    $logger->info("Le compte {user} à été crée | email => {mail} | Nom et prenom => {utilisateur} | heure => {date}", [
        'user'=>$this->getUser(),
        'utilisateur'=>strtoupper($user->getNom()). " ".$user->getPrenom(),
        'mail'=>$user->getEmail(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
   ]);
}

private function processConnexion($doctrine,$logger){
    $this->logToDatabase("L'utilisateur {user} s'est connecté", "INSCRIPTION",$doctrine,[
        'user'=>$this->getUser(),
   ],1);
    $logger->info("L'utilisateur {user} s'est connecté | heure => {date}", [
        'user'=>$this->getUser(),
        'date'=>(new \DateTime())->format('d/m/Y H:i:s'),
   ]);
}

}
