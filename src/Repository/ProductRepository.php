<?php

namespace App\Repository;

use App\Entity\Product;
use App\Model\SearchDataProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;




/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedByProductIdentifiant(): array
   {
       return $this->createQueryBuilder('p')
           ->orderBy('p.identifiant', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

   public function findAllOrderedByAllProduct(SearchDataProduct $searchDataProduct)
   {

    $productRepository = $this->createQueryBuilder('p');

    if(!empty($searchDataProduct->nom)){
        $productRepository = $productRepository
        ->andWhere('p.nom LIKE :nom')
        ->setParameter('nom', "%$searchDataProduct->nom%")
        ->orderBy('p.identifiant', 'ASC');

    }
            
            
    // dd($productRepository->getQuery()->getDQL());

            return $productRepository->getQuery()
            ->getResult();;

   }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
