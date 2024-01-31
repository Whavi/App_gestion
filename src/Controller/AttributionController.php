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


class AttributionController extends AbstractController
{
    #[Route('/gestion/attribution', name: 'user_gestion_attribution')]
    #[IsGranted('ROLE_USER')]
    public function gestionAttribution( AttributionRepository $attributionRepository, Request $request, PaginatorInterface $paginatorInterface) {

        $attribution = $attributionRepository->findAllOrderedByAttributionId();

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

        return $this->render('pages/user/attribution.html.twig', [
            'form' => $form->createView(),
            'attributions' => $posts,
        ],
        );
    }
    return $this->render('pages/user/attribution.html.twig', [
        'form' => $form->createView(),
        'attributions' => $posts,
    ]);
}
    

    #[Route('/gestion/attribution/delete/{id}', name: 'user_gestion_attribution_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_USER')]
    public function gestionAttributionEdit($id,AttributionRepository $attributionRepository, Request $request, EntityManagerInterface $manager) : Response {
        $attribution = $attributionRepository->find($id);

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
            return $this->redirectToRoute('user_gestion_attribution');


        }
       return $this->render('pages/user/edit/editAttribution.html.twig', [
            'attributions' => $attribution,
            'form' => $form->createView()
              ]);
    }



    #[Route('/gestion/attribution/send-email/{id}', name: 'user_gestion_send_mail')]
    #[IsGranted('ROLE_USER')]
    public function sendEmail($id,AttributionRepository $attributionRepository, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, UserRepository $userRepository, PdfGeneratorController $pdfGenerator, MailerInterface $mailer): Response
    {
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
        $filename = 'Bon_de_commande_N_' . $id . '.pdf';
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

        return $this->redirectToRoute('user_gestion_attribution');
    }

    #[Route('/gestion/attribution/addAttribution', name: 'user_gestion_newItemAttribution')]
    #[IsGranted('ROLE_USER')]
    public function addItemAttribution(EntityManagerInterface $em, Request $request, AttributionRepository $attributionRepository) : Response {
        
        $attribution = null; 

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
            $attribution->setPdfName("bon_de_commande_N" . $attribution->getId() . ".pdf");
            $em->persist($attribution);
            $em->flush();

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
        PdfGeneratorController $pdfGeneratorController,
        CollaborateurRepository $collaborateurRepository,
        ProductRepository $productRepository,
        AttributionRepository $attributionRepository,
        UserRepository $userRepository,
        YousignService $yousignService,
    ): Response {
        
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

        return $this->redirectToRoute('user_gestion_attribution');

    }
}
