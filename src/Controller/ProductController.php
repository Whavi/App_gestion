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

class ProductController extends AbstractController
{

############################################################################################################################
####################################################   PAGE D'ACCUEIL   ####################################################
############################################################################################################################
#[Route('/gestion', name: 'user_gestion')]
#[IsGranted('ROLE_USER')]
public function gestion(LoggerInterface $logger, ProductRepository $productRepository, Request $request, PaginatorInterface $paginatorInterface) {
   $data = $productRepository->findAllOrderedByProductIdentifiant();
   $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);

   $searchDataProduct = new SearchDataProduct();
   $form = $this->createForm(SearchTypeProduct::class, $searchDataProduct);
   $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
           $data = $productRepository->findAllOrderedByNameProduct($searchDataProduct);
           $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 12);
           $this->processProductRecherche($logger,$searchDataProduct); //LOG
           
           return $this->render('pages/user/home.html.twig', [ 
               'form' => $form->createView(),
               'role' => new User(),
               'listes' => $posts,]
            );
        }
    $this->processProduitAccueil($logger, $request); //LOG
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
public function gestionProductEdit($id,LoggerInterface $logger, ProductRepository $productRepository, Request $request, EntityManagerInterface $manager) : Response {
    $product = $productRepository->find($id);
    $form = $this->createForm(EditFormProductType::class, $product);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $this->processProduitEdit($product,$form->getData(), $manager, $logger);
       
        return $this->redirectToRoute('user_gestion');
    }
    $this->processProduitEntry($logger);
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
public function add_item(LoggerInterface $logger, EntityManagerInterface $manager, Request $request) : Response {
    $form = $this->createForm(UserFormProductType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->processProduitCreation( $form->getData(),$manager,$logger);
        return $this->redirectToRoute('user_gestion');
    }
    $this->processProduitCreationEntry($logger);
    return $this->render('pages/user/newItem/Product.html.twig', [
        'form' => $form->createView()
    ]);
}













############################################################################################################################
######################################################   FONCTION PRIVÉE   #################################################
############################################################################################################################


private function processProduitAccueil($logger, $request){   
    $page = $request->query->getInt('page', 1);
    $logger->info("{user} est rentré dans la page $page d'accueil Produit | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

private function processProductRecherche($logger,$searchDataProduct){
    $logger->info("{user} fait une recherche dans la page Produit | recherche => {rech} | heure => {date}", [
        'user' => $this->getUser(),
        'rech' => $searchDataProduct->getRecherche(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

public function processProduitDelete($product, $manager, $doctrine, $logger){
    $this->addFlash('success','Le produit a été supprimer');
    $manager = $doctrine->getManager();
    $manager->remove($product);
    $manager->flush();

    $logger->info("{user} a supprimer le produit suivant : Numéro de série {NumSeri} | Réf.Log => {ref} | Modèle => {mod} | catégorie => {cat} | heure de suppréssion => {date}", [
    'user'=>$this->getUser(),
    'NumSeri'=>$product->getIdentifiant(),
    'ref'=>$product->getRef(),
    'mod'=>$product->getNom(),
    'cat'=>$product->getCategory(),
    'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}


private function processProduitEdit($product, $data, $manager, $logger){
    $data->setUpdatedAt(new \DateTime());
    $this->addFlash(
        'success',
        'Votre produit a bien été modifier.'
    );
    $manager->persist($data);
    $manager->flush();

    $logger->info("{user} a modifié le produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat}| heure de changement : {date}", [
        'user'=>$this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}

private function processProduitCreation($data, $manager, $logger)
{
    $product = new Product();
    $product->setIdentifiant($data->getIdentifiant());
    $product->setNom($data->getNom());
    $product->setCategory($data->getCategory());
    $product->setUpdatedAt($data->getCreatedAt());
    $product->setRef($data->getRef());
    $this->addFlash(
     'success',
     'Votre produit a bien été crée.'
    );
    
    $manager->persist($product);
    $manager->flush();
    $logger->info("{user} a créé un produit : numéro de série => {NumSerie} | Rf. Log {ref} | Modèle => {mod} | category => {cat} heure de création : {date}", [
        'user' => $this->getUser(),
        'NumSerie'=>$product->getIdentifiant(),
        'ref'=>$product->getRef(),
        'mod'=>$product->getNom(),
        'cat'=>$product->getCategory(),
        'date'=>(new \DateTime)->format('d/m/Y H:i:s'),
    ]);
}


Private function processProduitEntry($logger){
    $logger->info("{user} est rentré dans la page d'édition de Produit | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}

Private function processProduitCreationEntry($logger){
    $logger->info("{user} est rentré dans la page d'ajout de Produit | heure => {date}", [
        'user' => $this->getUser(),
        'date' => (new \DateTime())->format('d/m/Y H:i:s'),
    ]);
}
}
