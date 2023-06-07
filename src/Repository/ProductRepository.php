<?php

namespace App\Repository;

use App\Entity\Product;
use App\Model\SearchDataProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \Knp\Component\Pager\PaginatorInterface;




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

   public function findBySearch(SearchDataProduct $searchDataProduct) : PaginatorInterface {
        $productRepository = $this->createQueryBuilder('p')
                ->where('p.nom LIKE :nom')
                ->setParameter('nom', '%NOM%')
                ;
                
                if(!empty($searchDataProduct->nom)){
                    $productRepository = $productRepository
                    ->andWhere('p.nom LIKE :nom')
                    ->setParameter('nom', "%($searchDataProduct->nom)%");
                }

                $productRepository = $productRepository
                ->getQuery()
                ->getResult();

                $pagination = $this->paginate($productRepository, $searchDataProduct->page, 9);

                return $pagination;
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
