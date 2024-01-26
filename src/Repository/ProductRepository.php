<?php

namespace App\Repository;

use App\Entity\Attribution;
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


   // SELECT p.nom, p.category, p.identifiant FROM product AS p INNER JOIN attribution AS a ON p.id = a.product_id WHERE a.id = 69
   public function findAllOrderedByInnerJoinProductContent($id): array
   {
        return $this->createQueryBuilder('p')
        ->select('p.nom, p.category, p.identifiant')
        ->innerJoin(Attribution::class, 'a', 'WITH', 'p.id = a.product')
        ->where('a.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getResult()
   ;
   }

   
   public function findAllOrderedByNameProduct(SearchDataProduct $searchDataProduct)
   {
    $productRepository = $this->createQueryBuilder('p');

    if(!empty(($searchDataProduct->ref or $searchDataProduct->identifiant))){
        $productRepository = $productRepository
        ->andWhere('p.ref LIKE :ref OR p.identifiant LIKE :identifiant' )
        ->setParameter('ref', "%$searchDataProduct->ref%")
        ->setParameter('identifiant', "%($searchDataProduct->identifiant)%")

        ->orderBy('p.identifiant', 'ASC');
    }
            return $productRepository->getQuery()->getResult();;

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
