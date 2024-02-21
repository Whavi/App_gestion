<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\edit\EditFormProductType;
use App\Form\search\SearchTypeProduct;
use App\Form\addItem\UserFormProductType;
use App\Model\SearchDataProduct;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Entity\LogEntry;

class ProductController extends AbstractController
{

############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion', name: 'user_gestion')]
#[IsGranted('ROLE_USER')]
public function gestion(LoggerInterface $logger, ProductRepository $productRepository, PersistenceManagerRegistry $doctrine,Request $request, PaginatorInterface $paginatorInterface) {
   $data = $productRepository->findAllOrderedByProductIdentifiant();
   $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);

   $searchDataProduct = new SearchDataProduct();
   $form = $this->createForm(SearchTypeProduct::class, $searchDataProduct);
   $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
           $data = $productRepository->findAllOrderedByNameProduct($searchDataProduct);
           $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
           $this->processProductRecherche($searchDataProduct, $doctrine, $logger); //LOG
           
           return $this->render('pages/user/home.html.twig', [ 
               'form' => $form->createView(),
               'role' => new User(),
               'listes' => $posts,]
            );
        }
    $this->processProduitAccueil($request, $doctrine, $logger); //LOG
    return $this->render('pages/user/home.html.twig', [ 
        'form' => $form->createView(),
        'role' => new User(),
        'listes' => $posts,
    ]);
}


############################################################################################################################
##################################################   PAGE DE SUPPRESSION   #################################################
############################################################################################################################
#[Route('/gestion/delete/{id}', name: 'user_gestion_delete')]
#[IsGranted('ROLE_ADMIN')]
public function gestionProductDelete($id, LoggerInterface $logger, ProductRepository $productRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
    $product = $productRepository->find($id);
    if ($product === null) {return $this->redirectToRoute('user_gestion');}
    $this->processProduitDelete($product, $manager, $doctrine, $logger);
    return $this->redirectToRoute('user_gestion');
}

############################################################################################################################
####################################################   PAGE D'ÉDITION   ####################################################
############################################################################################################################
#[Route('/gestion/edit/{id}', name: 'user_gestion_edit')]
#[IsGranted('ROLE_USER')]
public function gestionProductEdit($id,LoggerInterface $logger, ProductRepository $productRepository, PersistenceManagerRegistry $doctrine, Request $request, EntityManagerInterface $manager) : Response {
    $product = $productRepository->find($id);
    $form = $this->createForm(EditFormProductType::class, $product);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processProduitEdit($product,$form->getData(), $manager,$doctrine ,$logger);
       
        return $this->redirectToRoute('user_gestion');
    }
    $this->processProduitEntry($product, $doctrine, $logger);
    return $this->render('pages/user/edit/editProduct.html.twig', [
        'utilisateur' => $product,
        'form' => $form->createView()
    ]);
}


############################################################################################################################
####################################################   PAGE D'AJOUT   ######################################################
############################################################################################################################
#[Route('/gestion/addItem', name: 'user_gestion_newItemProduct')]
#[IsGranted('ROLE_USER')]
public function add_item(LoggerInterface $logger, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine, Request $request) : Response {
    $form = $this->createForm(UserFormProductType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->processProduitCreation( $form->getData(),$manager,$doctrine, $logger);
        return $this->redirectToRoute('user_gestion');
    }
    $this->processProduitCreationEntry($doctrine, $logger);
    return $this->render('pages/user/newItem/Product.html.twig', [
        'form' => $form->createView()
    ]);
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


private function processProduitAccueil($request, $doctrine,$logger ){   
    $page = $request->query->getInt('page', 1);
    $this->LogToDatabase("{user} est rentré dans la page $page d'accueil Produit", "PRODUIT",$doctrine,[
        'user' => $this->getUser(),
    ],0);
    $logger->info("{user} est rentré dans la page $page d'accueil Produit | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processProductRecherche($searchDataProduct, $doctrine, $logger){
    $this->logToDatabase("{user} fait une recherche dans la page Produit | recherche => {rech}","PRODUIT",$doctrine, [
        'user' => $this->getUser(),
        'rech' => $searchDataProduct->getRecherche(),
    ],4);
    $logger->info("{user} fait une recherche dans la page Produit | recherche => {rech} | heure => {date}", [
        'user' => $this->getUser(),
        'rech' => $searchDataProduct->getRecherche(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

public function processProduitDelete($product, $manager, $doctrine, $logger){
    $this->logToDatabase("{user} a supprimer le produit suivant : Numéro de série {NumSeri} | Réf.Log => {ref} | Modèle => {mod} | catégorie => {cat}","PRODUIT",$doctrine, [
        'user'=>$this->getUser(),
        'NumSeri'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        ],3);

    $logger->info("{user} a supprimer le produit suivant : Numéro de série {NumSeri} | Réf.Log => {ref} | Modèle => {mod} | catégorie => {cat} | heure de suppréssion => {date}", [
        'user'=>$this->getUser(),
        'NumSeri'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
        ]);
    
    $manager = $doctrine->getManager();
    $manager->remove($product);
    $manager->flush();
    $this->addFlash('success','Le produit a été supprimer');

    
}


private function processProduitEdit($product, $data, $manager, $doctrine, $logger){
    $data->setUpdatedAt(new \DateTime());
    $this->addFlash('success', 'Votre produit a bien été modifier.');
    $manager->persist($data);
    $manager->flush();

    $this->logToDatabase("{user} a modifié le produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat}", "PRODUIT",$doctrine,[
        'user'=>$this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
    ],2);
    $logger->info("{user} a modifié le produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat} | heure de changement : {date}", [
        'user'=>$this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

private function processProduitCreation($data, $manager,$doctrine, $logger)
{
    $product = new Product();
    $product->setIdentifiant($data->getIdentifiant());
    $product->setNom($data->getNom());
    $product->setCategory($data->getCategory());
    $product->setUpdatedAt($data->getCreatedAt());
    $product->setRef($data->getRef());
   
    
    $manager->persist($product);
    $manager->flush();

    $this->logToDatabase("{user} a créé un produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat}", "PRODUIT",$doctrine,[
        'user' => $this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
    ],1);
    $logger->info("{user} a créé un produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat} | heure de création : {date}", [
        'user' => $this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
    $this->addFlash('success', 'Votre produit a bien été crée.');
}


Private function processProduitEntry($product, $doctrine, $logger){
    $this->logToDatabase("{user} est rentré dans la page d'édition de Produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat} ", "PRODUIT",$doctrine,[
        'user' => $this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
    ],0);
    $logger->info("{user} est rentré dans la page d'édition de Produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat} | heure => {date}", [
        'user' => $this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

Private function processProduitCreationEntry($doctrine, $logger){
    $this->logToDatabase("{user} est rentré dans la page d'ajout de Produit", "PRODUIT",$doctrine,[
        'user' => $this->getUser(),
    ],0);
    $logger->info("{user} est rentré dans la page d'ajout de Produit | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
}
