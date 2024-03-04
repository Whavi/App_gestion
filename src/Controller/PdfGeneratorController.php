<?php
 
namespace App\Controller;
 
use App\Repository\AttributionRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;
use Symfony\Component\HttpFoundation\Request;
 
class PdfGeneratorController extends AbstractController
{

#[Route('/pdf/{id<\d+>}', name: 'user_gestion_attribution_pdf')]
#[IsGranted('ROLE_USER')]
public function index($id, PersistenceManagerRegistry $doctrine, LoggerInterface $logger, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository, UserRepository $userRepository): Response{
        $data = $this->getData($id, $collaborateurRepository, $productRepository, $attributionRepository, $userRepository);
        $html = $this->renderView('pages/user/pdf_generator/pdf.html.twig', $data);
        return $this->generatePdfResponse($html, $id, $doctrine, $logger);
}
public function generatePdfContent($id, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository, UserRepository $userRepository, LoggerInterface $logger): string{
        $data = $this->getData($id, $collaborateurRepository, $productRepository, $attributionRepository, $userRepository);
        $html = $this->renderView('pages/user/pdf_generator/pdf.html.twig', $data);
        return $this->generatePdfOutput($html, $logger);
    }

    
############################################################################################################################
########################################################  Signature   ######################################################
############################################################################################################################


#[Route('/gestion/attribution/{id}/signature/save-signature', name: 'user_gestion_attribution_save_signature', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function saveSignature($id ,Request $request, EntityManagerInterface $entityManager, AttributionRepository $repositoryAttribution): Response{
    $attribution = $repositoryAttribution->find($id);
    $data = $request->request->get('signature_data'); 
    $DataFinalB64 = base64_decode(explode(",", $data)[1]);   
    $filename = "signature_" . uniqid() . '.png';
    $filePath = $this->getParameter('kernel.project_dir') . '\public/sign\\' . $filename;
    
    file_put_contents($filePath, $DataFinalB64);
    
    $attribution->setSignatureImg($filename);
    $entityManager->flush();

    $this->addFlash('success', 'Signature saved successfully!');
    return $this->redirectToRoute('user_gestion_attribution_pdf', ['signature' => $filename, "id" => $id]);
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



private function imageToBase64($path){
    $img = $path;
    $type = pathinfo($img, PATHINFO_EXTENSION);
    $data = file_get_contents($img);
    if ($data !== false) {
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    } else { return null; }
}

private function getData($id, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository, UserRepository $userRepository)
{
    $collaborateur = $collaborateurRepository->findAllOrderedByInnerJoinNameContent($id);
    $product = $productRepository->findAllOrderedByInnerJoinProductContent($id);
    $attribution = $attributionRepository->findAllOrderedByInnerJoinDateAttributionContent($id);
    $descriptionAttribution = $attributionRepository->findAllOrderedByDescriptionAttribution($id);
    $user = $userRepository->findAllOrderedByInnerJoinNameContent($id);
    $name = $attributionRepository->findAllOrderedByInnerJoinNamePdfContent($id);
    $remarque = $attributionRepository->findAllOrderedByInnerJoinRemarqueContent($id);
    $signature = $attributionRepository->find($id);

    if ($signature && $signature->getSignatureImg() !== "Signer par API Yousign" && $signature->getSignatureImg() !== null && file_exists($this->getParameter('kernel.project_dir') . '/public/sign/' . $signature->getSignatureImg())) {
        $imageSignSrc = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/sign/' . $signature->getSignatureImg());
    } else {
        $imageSignSrc = null;
    }
    
    return [
        'imageSrc' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/navbar/images/SIF-Logo.png'),
        'collaborateurs' => $collaborateur,
        'attributions' => $attribution,
        'descriptions' => $descriptionAttribution,
        'products' => $product,
        'names' => $name,
        'users' => $user,
        'remarques' => $remarque,
        'imageSignSrc' => $imageSignSrc,
    ];
}

private function generatePdfResponse($html, $id, $doctrine, $logger): Response{
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $filename = 'Bon de commande N°' . $id . '.pdf';

    $this->logToDatabase("{user} a généré un PDF pour le bon de commande N°{id}",  "ATTRIBUTION",$doctrine,[
        'user' => $this->getUser(),
        'id' => $id,
    ],1);

    $logger->info("{user} a généré un PDF pour le bon de commande N°{id} le {date}", [
        'user' => $this->getUser(),
        'id' => $id,
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
    return new Response(
        $dompdf->stream($filename, ['Attachment' => false]),
        Response::HTTP_OK,
        ['Content-Type' => 'application/pdf']
    );
}

private function generatePdfOutput($html, $logger): string{
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $logger->info("{user} a généré un PDF le {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
    return $dompdf->output();
}

}