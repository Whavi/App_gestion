<?php
 
namespace App\Controller;
 
use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Repository\AttributionRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
 
class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/{id<\d+>}', name: 'user_gestion_attribution_pdf')]
    public function index($id, CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository, UserRepository $userRepository): Response
    {
        $collaborateur = $collaborateurRepository->findAllOrderedByInnerJoinNameContent($id);
        $product = $productRepository->findAllOrderedByInnerJoinProductContent($id);
        $attribution = $attributionRepository->findAllOrderedByInnerJoinDateAttributionContent($id);
        $descriptionAttribution = $attributionRepository->findAllOrderedByDescriptionAttribution($id);
        $user = $userRepository->findAllOrderedByInnerJoinNameContent($id);
        $remarque = $attributionRepository->findAllOrderedByInnerJoinRemarqueContent($id);


        $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/navbar/images/SIF-Logo.png'),
            'collaborateurs' => $collaborateur,
            'attributions' => $attribution,
            'descriptions' => $descriptionAttribution,
            'products' => $product,
            'users' => $user,
            'remarques' => $remarque,
            
        ];
        $html =  $this->renderView('pages/user/pdf_generator/pdf.html.twig', $data);

        $contxt = stream_context_create([ 
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ]
        ]);
        
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setHttpContext($contxt);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        
        $dompdf->output();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response (
            $dompdf->stream('Bon_de_commande', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
 
    private function imageToBase64($path) {
        $img = $path;
        $type = pathinfo($img, PATHINFO_EXTENSION);
        $data = file_get_contents($img);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}