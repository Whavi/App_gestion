<?php
 
namespace App\Controller;
 
use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Repository\AttributionRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
 
class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/{id}', name: 'user_gestion_attribution_pdf')]
    public function index(CollaborateurRepository $collaborateurRepository, ProductRepository $productRepository, AttributionRepository $attributionRepository): Response
    {
        $collaborateur = new Collaborateur();
        $collaborateurId = $collaborateur->getId(); 
        $collaborateur = $collaborateurRepository->findAll();
        $product = $productRepository->findAll();
        $attribution = $attributionRepository->findAll();

        $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/navbar/images/SIF.jpeg'),
            'collaborateurs' => $collaborateur,
            'attributions' => $attribution
            
        ];
        $html =  $this->renderView('pages/user/pdf_generator/pdf.html.twig', $data);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
         
        return new Response (
            $dompdf->stream('Bon_de_commande', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
 
    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}