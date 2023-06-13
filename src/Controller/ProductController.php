<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\EditFormProductType;
use App\Form\SearchTypeProduct;
use App\Form\UserFormProductType;
use App\Model\SearchDataProduct;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class ProductController extends AbstractController
{
    #[Route('/gestion', name: 'user_gestion')]
    ##[IsGranted('ROLE_USER')]
    public function gestion(ProductRepository $productRepository, Request $request, PaginatorInterface $paginatorInterface) {

       $data = $productRepository->findAllOrderedByProductIdentifiant();

       $posts = $paginatorInterface->paginate(
           $data,
           $request->query->getInt('page', 1),
           6
       );

       $searchDataProduct = new SearchDataProduct();
       $form = $this->createForm(SearchTypeProduct::class, $searchDataProduct);

       $form->handleRequest($request);
           if($form->isSubmitted() && $form->isValid()){
               $data = $productRepository->findAllOrderedByNameProduct($searchDataProduct);
           
               $posts = $paginatorInterface->paginate(
                   $data,
                   $request->query->getInt('page', 1),
                   6);
               
               return $this->render('pages/user/home.html.twig', [ 
                   'form' => $form->createView(),
                   'listes' => $posts,]);
               }
               
       return $this->render('pages/user/home.html.twig', [ 
           'form' => $form->createView(),
           'listes' => $posts,]);
       }
   
   #[Route('/gestion/delete/{id}', name: 'user_gestion_delete')]
   public function gestionProductDelete($id, ProductRepository $productRepository, EntityManagerInterface $manager, PersistenceManagerRegistry $doctrine) : Response {
       $product = $productRepository->find($id);
       if ($product === null) {
           return $this->redirectToRoute('user_gestion');
           }

       $this->addFlash('success','Le produit a été supprimer');
       $manager = $doctrine->getManager();
       $manager->remove($product);
       $manager->flush();
   
       return $this->redirectToRoute('user_gestion');
   }

   #[Route('/gestion/edit/{id}', name: 'user_gestion_edit')]
   public function gestionProductEdit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $manager) : Response {
      $product = $productRepository->find($id);

       $form = $this->createForm(EditFormProductType::class, $product);
       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()){
           $productdata = $form->getData();
           $productdata->setUpdatedAt(new \DateTime());

           $this->addFlash(
               'success',
               'Votre compte a bien été modifier.'
           );

           $manager->persist($productdata);
           $manager->flush();
           return $this->redirectToRoute('user_gestion');


       }
      return $this->render('pages/user/edit/editProduct.html.twig', [
           'utilisateur' => $product,
           'form' => $form->createView()
             ]);
   }


   #[Route('/gestion/addItem', name: 'user_gestion_newItemProduct')]
   public function add_item(EntityManagerInterface $em, Request $request) : Response {

       $form = $this->createForm(UserFormProductType::class);
       $form->handleRequest($request);
       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           
           $product = new Product();
           $product->setIdentifiant($data->getIdentifiant());
           $product->setNom($data->getNom());
           $product->setCategory($data->getCategory());
           $product->setUpdatedAt($data->getCreatedAt());

           $em->persist($product);
           $em->flush();


           return $this->redirectToRoute('user_gestion');

   }
   return $this->render('pages/user/newItem/Product.html.twig', [
       'form' => $form->createView()
   ]);
}

}
