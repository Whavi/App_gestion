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


class AttributionController extends AbstractController
{
    #[Route('/gestion/{currentFunction}/attribution/', name: 'user_gestion_attribution', defaults: ['currentFunction' => 'nouvellesAttributions'])]    
    #[IsGranted('ROLE_USER')]
    public function gestionAttribution( AttributionRepository $attributionRepository, Request $request, PaginatorInterface $paginatorInterface, LoggerInterface $logger ,$currentFunction) {
        $currentDateTime = new \DateTime();
        if ($currentFunction === 'nouvellesAttributions') {
            $attribution = $attributionRepository->findAllOrderedByAttributionId();
        } else {
            $attribution = $attributionRepository->findOldAttributions();
        }

        $logger->info("{user} est rentré dans la page d'accueil {cfunc} | heure => {date}", 
        [
        'user'=>$this->getUser(),
        'cfunc'=>$currentFunction, 
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
    

        $posts = $paginatorInterface->paginate(
            $attribution,
            $request->query->getInt('page', 1),
            12
        );

        $searchDataAttribution = new SearchDataAttribution();
        $form = $this->createForm(SearchTypeAttributionType::class, $searchDataAttribution);

        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $attributionRepository->findAllOrderedByNameAttribution($searchDataAttribution);
                $posts = $paginatorInterface->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    12);
                $logger->info("{user} fait une recherche dans la page Attribution | recherche => {rech} | heure => {date}", 
                [
                'user'=>$this->getUser(),
                'rech'=>$searchDataAttribution->getId(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);

        return $this->render('pages/user/attribution.html.twig', [
            'form' => $form->createView(),
            'attributions' => $posts,
            'currentFunction' => $currentFunction,
        ],
        );
    }
    return $this->render('pages/user/attribution.html.twig', [
        'form' => $form->createView(),
        'attributions' => $posts,
        'currentFunction' => $currentFunction,
    ]);
}
    

    #[Route('/gestion/attribution/delete/{id}', name: 'user_gestion_attribution_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function gestionAttributionDelete($id, LoggerInterface $logger,  AttributionRepository $attributionRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
        
        $currentDateTime = new \DateTime();
        $attribution = $attributionRepository->find($id);
        if ($attribution === null) {
            return $this->redirectToRoute('user_gestion_attribution');
            }

        $this->addFlash('success',"Le département a été supprimer");
        $manager = $doctrine->getManager();
        $manager->remove($attribution);
        $manager->flush();
        $logger->info("{user} a supprimer l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | date d'attribution => {att} | date de restitution => {res} | heure de suppréssion => {date}", 
        ['id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
        'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
        return $this->redirectToRoute('user_gestion_attribution');
    }


    #[Route('/gestion/attribution/edit/{id}', name: 'user_gestion_attribution_edit')]
    #[IsGranted('ROLE_USER')]
    public function gestionAttributionEdit($id, LoggerInterface $logger, AttributionRepository $attributionRepository, Request $request, EntityManagerInterface $manager) : Response {
        $attribution = $attributionRepository->find($id);
        $currentDateTime = new \DateTime();
        $logger->info("{user} est rentré dans la page d'édition de l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem} | heure : {date}", 
        ['id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mod'=>$attribution->getProduct()->getNom(),
        'cat'=>$attribution->getProduct()->getCategory(),
        'des'=>$attribution->getDescriptionProduct(),
        'rem'=>$attribution->getRemarque(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
        $form = $this->createForm(EditFormAttributionType::class, $attribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $attribution->setUpdatedAt(new \DateTime());
            $attribution->setByUser($this->getUser()); 

            $this->addFlash(
                'success',
                'Votre attribution a bien été modifier.'
            );

            $manager->persist($data);
            $manager->flush();
            $logger->info("{user} à modifier l'id : {id} | Collaborateur => {collab} | Modèle => {mod} | catégorie => {cat} | description => {des} | remarques => {rem} | heure de changement : {date}", 
                ['id'=> $id,
                'user'=>$this->getUser(),
                'collab'=>$attribution->getCollaborateur(),
                'mod'=>$attribution->getProduct()->getNom(),
                'cat'=>$attribution->getProduct()->getCategory(),
                'des'=>$attribution->getDescriptionProduct(),
                'rem'=>$attribution->getRemarque(),
                'att'=>$attribution->getDateAttribution()->format('d/m/Y H:i:s'),
                'res'=>$attribution->getDateRestitution()->format('d/m/Y H:i:s'), 
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);
            return $this->redirectToRoute('user_gestion_attribution');
            

        }
       return $this->render('pages/user/edit/editAttribution.html.twig', [
            'attributions' => $attribution,
            'form' => $form->createView()
              ]);
    }



    #[Route('/gestion/attribution/send-email/{id}', name: 'user_gestion_send_mail')]
    #[IsGranted('ROLE_USER')]
    public function sendEmail($id, LoggerInterface $logger, AttributionRepository $attributionRepository, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, UserRepository $userRepository, PdfGeneratorController $pdfGenerator, MailerInterface $mailer): Response
    {
        $currentDateTime = new \DateTime();
        $attribution = $attributionRepository->find($id);
        $collaborateur = $attribution->getCollaborateur();
        $collaborateurEmail = $collaborateur ? $collaborateur->getEmail() : 'it@secours-islamique.org';

        $pdfContent = $pdfGenerator->generatePdfContent(
            $id,
            $collaborateurRepository,
            $productRepository,
            $attributionRepository,
            $userRepository
        );
        $filename = 'Bon de commande N°' . $id . '.pdf';
        $email = (new TemplatedEmail())
            ->from('it@secours-islamique.org')
            ->to($collaborateurEmail)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Bon de commande du prêt de matériel')
            ->attach($pdfContent, $filename, 'application/pdf')
            ->htmlTemplate('pages/user/mail/mailpdf.html.twig');
           
            try {
                $mailer->send($email);
            } catch (\Exception $e) {
                dump($e->getMessage()); // Log or print the exception message
            }

        $this->addFlash(
            'success',
            "L'email a bien été envoyer."
        );
        $logger->info("{user} a envoyer un email à l'id : {id} | Collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | heure d'envoi du mail : {date}", 
        ['id'=> $id,
        'user'=>$this->getUser(),
        'collab'=>$attribution->getCollaborateur(),
        'mail'=>$attribution->getCollaborateur()->getEmail(),
        'cat'=>$attribution->getPdfName(),
        'dep'=>$attribution->getCollaborateur()->getDepartement(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);
        return $this->redirectToRoute('user_gestion_attribution');
    }



    #[Route('/gestion/attribution/addAttribution', name: 'user_gestion_newItemAttribution')]
    #[IsGranted('ROLE_USER')]
    public function addItemAttribution(LoggerInterface $logger ,EntityManagerInterface $em, Request $request) : Response {
        $currentDateTime = new \DateTime();
        $attribution = null; 

        $logger->info("{user} est rentré dans la page d'ajout d'Attribution | heure : {date}", 
        [
        'user'=>$this->getUser(),
        'date'=>$currentDateTime->format('d/m/Y H:i:s'),
    ]);

        $form = $this->createForm(UserFormAttributionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
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
            $this->addFlash(
                'success',
                'Votre attribution a bien été crée.'
            );

            $em->persist($attribution);
            $em->flush();

            // Set the pdf_name based on the id and update the entity
            $attribution->setPdfName("Bon de commande N°" . $attribution->getId() . ".pdf");
            $em->persist($attribution);
            $em->flush();
            $logger->info("{user} a crée une attribution à au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | heure de création : {date}", 
                [
                'user'=>$this->getUser(),
                'collab'=>$attribution->getCollaborateur(),
                'mail'=>$attribution->getCollaborateur()->getEmail(),
                'cat'=>$attribution->getId(),
                'dep'=>$attribution->getCollaborateur()->getDepartement(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);

            return $this->redirectToRoute('user_gestion_attribution');
    }
    return $this->render('pages/user/newItem/Attribution.html.twig', [
        'attributions' => $attribution,
        'form' => $form->createView()]);
    }

    #[Route('/gestion/attribution/signature/{id}', name: 'user_gestion_sign')]
    #[IsGranted('ROLE_USER')]
    public function signature(
        $id,
        LoggerInterface $logger,
        PdfGeneratorController $pdfGeneratorController,
        CollaborateurRepository $collaborateurRepository,
        ProductRepository $productRepository,
        AttributionRepository $attributionRepository,
        UserRepository $userRepository,
        YousignService $yousignService,
    ): Response {
        $currentDateTime = new \DateTime();
        $collaborateurs = $collaborateurRepository->findAllOrderedByInnerJoin_Name_Mail_ContentContrat($id);
        foreach ($collaborateurs as $collaborateur) {
            $email = $collaborateur->getEmail();
            $prenom = $collaborateur->getPrenom();
            $nom = $collaborateur->getNom();
        }

        $attribut = $attributionRepository->find($id);

        $pdfContent = $pdfGeneratorController->generatePdfContent(
            $id,
            $collaborateurRepository,
            $productRepository,
            $attributionRepository,
            $userRepository,
        );
        
        // Enregistrez le contenu du PDF dans un fichier local
        $filename = 'Bon de commande N°' . $id . '.pdf';
        $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;

        // Utilisez file_put_contents pour écrire le contenu dans le fichier
        file_put_contents($pdfFilePath, $pdfContent);

        $yousignSignatureRequest = $yousignService->signatureRequest($id);
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

        $logger->info("{user} a crée une signature au collaborateur => {collab} | email => {mail} | numéro de commande => {cat} | département => {dep} | signature_id => {signId} | Document_id => {docId} | signer_id => {sID} | heure de création : {date}", 
                [
                'user'=>$this->getUser(),
                'collab'=>$attribut->getCollaborateur(),
                'mail'=>$attribut->getCollaborateur()->getEmail(),
                'cat'=>$attribut->getId(),
                'dep'=>$attribut->getCollaborateur()->getDepartement(),
                'signId'=>$attribut->getSignatureId(),
                'docId'=>$attribut->getDocumentId(),
                'sID'=>$attribut->getSignerId(),
                'date'=>$currentDateTime->format('d/m/Y H:i:s'),
            ]);
        return $this->redirectToRoute('user_gestion_attribution');

    }
}
