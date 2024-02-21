<?php

namespace App\Controller;

use App\Entity\Attribution;
use App\Service\YousignService;
use App\Form\edit\EditFormAttributionType;
use App\Form\search\SearchTypeAttributionType;
use App\Form\addItem\UserFormAttributionType;
use App\Model\SearchDataAttribution;
use App\Repository\AttributionRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Controller\PdfGeneratorController;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;



class AttributionController extends AbstractController
{
############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion/{currentFunction}/attribution/', name: 'user_gestion_attribution', defaults: ['currentFunction' => 'nouvellesAttributions'])]    
#[IsGranted('ROLE_USER')]
public function gestionAttribution( AttributionRepository $attributionRepository, Request $request, PersistenceManagerRegistry $doctrine, PaginatorInterface $paginatorInterface, LoggerInterface $logger ,$currentFunction) {
    if ($currentFunction === 'nouvellesAttributions') { $attribution = $attributionRepository->findAllOrderedByAttributionId();
    } else { $attribution = $attributionRepository->findOldAttributions(); }

    $posts = $paginatorInterface->paginate($attribution, $request->query->getInt('page', 1), 12);
    $searchDataAttribution = new SearchDataAttribution();
    $form = $this->createForm(SearchTypeAttributionType::class, $searchDataAttribution);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()){
        $data = $attributionRepository->findAllOrderedByNameAttribution($searchDataAttribution);
        $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
        $this->processAttributionRecherche($searchDataAttribution, $doctrine, $logger);
        return $this->render('pages/user/attribution.html.twig', [
            'form' => $form->createView(),
            'attributions' => $posts,
            'currentFunction' => $currentFunction,
            ],
        );
    }
    $this->processAttributionAccueilEntry($currentFunction, $doctrine, $request,$logger);
    return $this->render('pages/user/attribution.html.twig', [
        'form' => $form->createView(),
        'attributions' => $posts,
        'currentFunction' => $currentFunction,
    ]);
}

############################################################################################################################
##################################################   PAGE DE SUPPRESSION   #################################################
############################################################################################################################
#[Route('/gestion/attribution/delete/{id}', name: 'user_gestion_attribution_delete', methods: ['GET', 'DELETE'])]
#[IsGranted('ROLE_ADMIN')]
public function gestionAttributionDelete($id, LoggerInterface $logger,  AttributionRepository $attributionRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
    $attribution = $attributionRepository->find($id);
    if ($attribution === null) { return $this->redirectToRoute('user_gestion_attribution'); }
    $this->processAttributionDelete($attribution, $manager, $id, $doctrine, $logger);
    return $this->redirectToRoute('user_gestion_attribution');
}

############################################################################################################################
####################################################   PAGE D'ÉDITION   ####################################################
############################################################################################################################
#[Route('/gestion/attribution/edit/{id}', name: 'user_gestion_attribution_edit')]
#[IsGranted('ROLE_USER')]
public function gestionAttributionEdit($id, LoggerInterface $logger, AttributionRepository $attributionRepository, Request $request,PersistenceManagerRegistry $doctrine, EntityManagerInterface $manager) : Response {
    $attribution = $attributionRepository->find($id);
    $form = $this->createForm(EditFormAttributionType::class, $attribution);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processAttributionEdit($attribution, $form->getData(), $manager,  $doctrine,$id, $logger);
        return $this->redirectToRoute('user_gestion_attribution');
    }
    $this->processAttributionEditEntry( $doctrine,$attribution, $id, $logger);
    return $this->render('pages/user/edit/editAttribution.html.twig', [
        'attributions' => $attribution,
        'form' => $form->createView()
    ]);
}


############################################################################################################################
#####################################################   ENVOI MAIL  ########################################################
############################################################################################################################

#[Route('/gestion/attribution/send-email/{id}', name: 'user_gestion_send_mail')]
#[IsGranted('ROLE_USER')]
public function sendEmail($id, LoggerInterface $logger,PersistenceManagerRegistry $doctrine, AttributionRepository $attributionRepository, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, UserRepository $userRepository, PdfGeneratorController $pdfGenerator, MailerInterface $mailer): Response
{
    $attribution = $attributionRepository->find($id);
    $collaborateur = $collaborateurRepository->find($id);
    $collaborateurEmail = $collaborateur ? $collaborateur->getEmail() : 'it@secours-islamique.org';
    $pdfContent = $pdfGenerator->generatePdfContent($id, $collaborateurRepository, $productRepository, $attributionRepository, $userRepository,$doctrine, $logger);
    $filename = 'Bon de commande N°' . $id . '.pdf';
    $email = (new TemplatedEmail())
        ->from('it@secours-islamique.org')
        ->to($collaborateurEmail)
        ->priority(Email::PRIORITY_HIGH)
        ->subject('Bon de commande du prêt de matériel')
        ->attach($pdfContent, $filename, 'application/pdf')
        ->htmlTemplate('pages/user/mail/mailpdf.html.twig');  
    $mailer->send($email);    
    $this->processAttributionSenMail($attribution, $id, $doctrine, $logger);
    return $this->redirectToRoute('user_gestion_attribution');
}

############################################################################################################################
####################################################   PAGE D'AJOUT   ######################################################
############################################################################################################################
#[Route('/gestion/attribution/addAttribution', name: 'user_gestion_newItemAttribution')]
#[IsGranted('ROLE_USER')]
public function addItemAttribution(LoggerInterface $logger ,PersistenceManagerRegistry $doctrine,EntityManagerInterface $manager, Request $request) : Response {
    $attribution = null; 
    $form = $this->createForm(UserFormAttributionType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->processAttributionCreation($attribution, $form->getData(), $doctrine, $manager, $logger);            
        return $this->redirectToRoute('user_gestion_attribution');
    }
    $this->processAttributionCreationEntry( $doctrine,$logger);
    return $this->render('pages/user/newItem/Attribution.html.twig', [
        'attributions' => $attribution,
        'form' => $form->createView()]);
    }
############################################################################################################################
############################################   SIGNATURE ELECTRONIQUE   ####################################################
############################################################################################################################
#[Route('/gestion/attribution/signature/{id}', name: 'user_gestion_sign')]
#[IsGranted('ROLE_USER')]
public function signature( $id,LoggerInterface $logger,PersistenceManagerRegistry $doctrine, PdfGeneratorController $pdfGeneratorController, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository, UserRepository $userRepository, YousignService $yousignService): Response {
    $collaborateurs = $collaborateurRepository->findAllOrderedByInnerJoin_Name_Mail_ContentContrat($id);
    foreach ($collaborateurs as $collaborateur) {
        $email = $collaborateur->getEmail();
        $prenom = $collaborateur->getPrenom();
        $nom = $collaborateur->getNom();
    }
    $attribut = $attributionRepository->find($id);
    $this->procressAttributionSiganture($attribut, $collaborateurs ,$email, $prenom, $nom, $attributionRepository, $collaborateurRepository, $pdfGeneratorController, $userRepository, $productRepository, $yousignService,$doctrine, $logger);
    return $this->redirectToRoute('user_gestion_attribution');
}









############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################

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


private function processAttributionAccueilEntry($currentFunction, $doctrine,$request, $logger){
    $page = $request->query->getInt('page', 1);

    $this->LogToDatabase("{user} est rentré dans la page $page d'accueil {cfunc} ","ATTRIBUTION", $doctrine,[
        'user'=>$this->getUser(),
        'cfunc'=>$currentFunction, 
    ],0);

    $logger->info("{user} est rentré dans la page $page d'accueil {cfunc} | heure => {date}", [
        'user'=>$this->getUser(),
        'cfunc'=>$currentFunction, 
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}


private function processAttributionRecherche($searchDataAttribution, $doctrine, $logger){
    $this->LogToDatabase("{user} fait une recherche dans la page Attribution | recherche => {rech}", "ATTRIBUTION", $doctrine, [
        'user'=>$this->getUser(),
        'rech'=>$searchDataAttribution->getId(),
    ],4);

    $logger->info("{user} fait une recherche dans la page Attribution | recherche => {rech} | heure => {date}", [
        'user'=>$this->getUser(),
        'rech'=>$searchDataAttribution->getId(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

private function processAttributionDelete($attribution, $manager, $id, $doctrine, $logger){
    $this->LogToDatabase("{user} a supprimer l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | date d'attribution => {att} | date de restitution => {res}", "ATTRIBUTION", $doctrine,[
        'id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
        'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
        ],3);
       
    $logger->info("{user} a supprimer l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | date d'attribution => {att} | date de restitution => {res} | heure de suppréssion => {date}", [
    'id'=> $id,
    'user'=>$this->getUser(),
    'collab'=>$attribution->getCollaborateur(),
    'mod'=>$attribution->getProduct()->getNom(),
    'cat'=>$attribution->getProduct()->getCategory(),
    'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
    'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
    'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);

    $manager = $doctrine->getManager();
    $manager->remove($attribution);
    $manager->flush();

    $this->addFlash('success',"Le département a été supprimer");

}

private function processAttributionEdit($attribution, $data, $manager, $doctrine,$id, $logger){
    $attribution->setUpdatedAt(new \DateTime());
    $attribution->setByUser($this->getUser()); 


    $this->LogToDatabase("{user} à modifier l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem}", "ATTRIBUTION", $doctrine,[
        'id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'des'=>$attribution->getDescriptionProduct(),
        'rem'=>$attribution->getRemarque(),
        'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
        'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
    ],2);

    $logger->info("{user} à modifier l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem} | heure de changement : {date}", [
        'id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'des'=>$attribution->getDescriptionProduct(),
        'rem'=>$attribution->getRemarque(),
        'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
        'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);

    $manager->persist($data);
    $manager->flush();

    $this->addFlash('success','Votre attribution a bien été modifier.');

}

private function processAttributionSenMail($attribution, $id, $doctrine, $logger){
    $this->addFlash('success', "L'email a bien été envoyer.");
    $this->LogToDatabase("{user} a envoyer un email à l'id : {id} | Collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep}", "ATTRIBUTION", $doctrine,[
        'id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mail'=>$attribution->getCollaborateur()->getEmail(),
        'cat'=>$attribution->getPdfName(),
        'dep'=>$attribution->getCollaborateur()->getDepartement(),
    ],1);

    $logger->info("{user} a envoyer un email à l'id : {id} | Collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | heure d'envoi du mail : {date}", [
    'id'=> $id,
    'user'=>$this->getUser(),
    'collab'=>$attribution->getCollaborateur(),
    'mail'=>$attribution->getCollaborateur()->getEmail(),
    'cat'=>$attribution->getPdfName(),
    'dep'=>$attribution->getCollaborateur()->getDepartement(),
    'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

private function processAttributionCreation($attribution, $data,  $doctrine,$manager, $logger){
    $attribution = new Attribution();
    $attribution->setCreatedAt(new \DateTime());
    $attribution->setUpdatedAt(new \DateTime());
    $attribution->setDateAttribution($data->getDateAttribution());
    $attribution->setDateRestitution($data->getDateRestitution());
    $attribution->setDescriptionProduct($data->getDescriptionProduct());
    $attribution->setRemarque($data->getRemarque());
    $attribution->setCollaborateur($data->getCollaborateur());
    $attribution->setByUser($this->getUser()); 
    $attribution->setProduct($data->getProduct()); 

    $manager->persist($attribution);
    $manager->flush();

    $attribution->setPdfName("Bon de commande N°" . $attribution->getId() . ".pdf");
    $manager->persist($attribution);
    $manager->flush();

    $this->LogToDatabase("{user} a crée une attribution à au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep}","ATTRIBUTION", $doctrine, [
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mail'=>$attribution->getCollaborateur()->getEmail(),
        'cat'=>$attribution->getId(),
        'dep'=>$attribution->getCollaborateur()->getDepartement(),
    ],1);

    $logger->info("{user} a crée une attribution à au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | heure de création : {date}", [
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mail'=>$attribution->getCollaborateur()->getEmail(),
        'cat'=>$attribution->getId(),
        'dep'=>$attribution->getCollaborateur()->getDepartement(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);

    $this->addFlash('success', 'Votre attribution a bien été crée.');

}

private function procressAttributionSiganture($attribut,$collaborateurs,$email, $prenom, $nom, $attributionRepository, $collaborateurRepository, $pdfGeneratorController, $userRepository, $productRepository, $yousignService, $doctrine, $logger){
    $pdfContent = $pdfGeneratorController->generatePdfContent($attribut->getId(), $collaborateurRepository, $productRepository, $attributionRepository, $userRepository, $logger);
    
    $filename = 'Bon de commande N°' . $attribut->getId() . '.pdf';
    $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
    file_put_contents($pdfFilePath, $pdfContent);

    $yousignSignatureRequest = $yousignService->signatureRequest($attribut->getId());
    $SignatureIdRequest = json_decode($yousignSignatureRequest);
    $attribut->setSignatureId($SignatureIdRequest->id);
    $attributionRepository->save($attribut, true);

    $uploadDocument = $yousignService->uploadDocument($attribut->getSignatureId(), $attribut->getPdfName());
    $documentIdRequest = json_decode($uploadDocument);
    $attribut->setDocumentId($documentIdRequest->id);
    $attributionRepository->save($attribut, true);

    foreach ($collaborateurs as $collaborateur) {
            $signerId = $yousignService->addSigner(
            $attribut->getSignatureId(),
            $attribut->getDocumentId(),
            $email,
            $prenom,
            $nom
        );
    }

    $signerIdRequest = json_decode($signerId);
    $attribut->setSignerId($signerIdRequest->id);
    $attributionRepository->save($attribut, true);

    $yousignService->activateSignatureRequest($attribut->getSignatureId());

    $this->LogToDatabase("{user} a crée une signature au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | signature_id => {signId} | Document_id => {docId} | signer_id => {sID}", 
    "ATTRIBUTION", $doctrine,[
        'user' => $this->getUser(),
        'collab' => $attribut->getCollaborateur(),
        'mail' => $attribut->getCollaborateur()->getEmail(),
        'cat' => $attribut->getId(),
        'dep' => $attribut->getCollaborateur()->getDepartement(),
        'signId' => $attribut->getSignatureId(),
        'docId' => $attribut->getDocumentId(),
        'sID' => $attribut->getSignerId(),
    ],1);
    $logger->info("{user} a crée une signature au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | signature_id => {signId} | Document_id => {docId} | signer_id => {sID} | heure de création : {date}", 
        [
        'user' => $this->getUser(),
        'collab' => $attribut->getCollaborateur(),
        'mail' => $attribut->getCollaborateur()->getEmail(),
        'cat' => $attribut->getId(),
        'dep' => $attribut->getCollaborateur()->getDepartement(),
        'signId' => $attribut->getSignatureId(),
        'docId' => $attribut->getDocumentId(),
        'sID' => $attribut->getSignerId(),
        'date' => (new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

private function processAttributionCreationEntry( $doctrine,$logger){
    $this->logToDatabase("{user} est rentré dans la page d'ajout d'Attribution", "ATTRIBUTION", $doctrine,[
        'user'=>$this->getUser(),
     ],0);
    $logger->info("{user} est rentré dans la page d'ajout d'Attribution | heure : {date}", [
        'user'=>$this->getUser(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
     ]);

}

private function processAttributionEditEntry( $doctrine,$attribution, $id, $logger){
    $this->logToDatabase("{user} est rentré dans la page d'édition de l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem}", "ATTRIBUTION", $doctrine,[
        'id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'des'=>$attribution->getDescriptionProduct(),
        'rem'=>$attribution->getRemarque(),
        ],0);
    $logger->info("{user} est rentré dans la page d'édition de l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem} | heure : {date}", [
    'id'=> $id,
    'user'=>$this->getUser(),
    'collab'=>$attribution->getCollaborateur(),
    'mod'=>$attribution->getProduct()->getNom(),
    'cat'=>$attribution->getProduct()->getCategory(),
    'des'=>$attribution->getDescriptionProduct(),
    'rem'=>$attribution->getRemarque(),
    'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

}

