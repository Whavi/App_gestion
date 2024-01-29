<?php

namespace App\Repository;

use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Entity\User;
use App\Model\SearchDataAttribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attribution>
 *
 * @method Attribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribution[]    findAll()
 * @method Attribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribution::class);
    }

    public function save(Attribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Attribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedByAttributionId(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllOrderedByInnerJoinProduit(): array
    {
        return $this->createQueryBuilder('a')
         ->select('p.nom')
         ->select('p.category')
         ->innerJoin(Product::class, 'p', 'WITH', 'p.id = a.id')
         ->getQuery()
         ->getResult()
        ;
    }

    public function findAllOrderedByNameAttribution(SearchDataAttribution $searchDataAttribution)
   {
    $collaborateurRepository = $this->createQueryBuilder('a');

    if(!empty(($searchDataAttribution->id))){
        $collaborateurRepository = $collaborateurRepository
        ->andWhere('a.id LIKE :id' )
        ->setParameter('id', "%$searchDataAttribution->id%")
        ->orderBy('a.id', 'ASC');

    }else{
        
        $collaborateurRepository = $collaborateurRepository
        ->orderBy('a.id', 'DESC');
    }
    return $collaborateurRepository->getQuery()
        ->getResult();

   }


    public function findAllOrderedByInnerJoinCollaborateur(): array
    {
        return $this->createQueryBuilder('a')
         ->select('c.nom')
         ->select('c.prenom')
         ->innerJoin(Collaborateur::class, 'c', 'WITH', 'c.id = a.id')
         ->getQuery()
         ->getResult()
        ;
    }


   // SELECT a.date_attribution, a.date_restitution FROM attribution AS a WHERE a.id = $id
   public function findAllOrderedByInnerJoinDateAttributionContent($id): array
   {
        return $this->createQueryBuilder('a')
        ->select('a.dateAttribution', 'a.dateRestitution')
        ->where('a.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getResult()
   ;
   }

   // SELECT a.descrpition_product FROM attribution AS a WHERE a.id = $id
   public function findAllOrderedByDescriptionAttribution($id): array
   {
        return $this->createQueryBuilder('a')
        ->select('a.descriptionProduct')
        ->where('a.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getResult()
   ;
   }

      // SELECT a.descrpition_product FROM attribution AS a WHERE a.id = $id
      public function findAllOrderedByInnerJoinNamePdfContent($id): array
      {
           return $this->createQueryBuilder('a')
           ->select('a.id')
           ->where('a.id = :id')
           ->setParameter('id', $id)
           ->getQuery()
           ->getResult()
      ;
      }

   public function findAllOrderedByInnerJoinRemarqueContent($id): array
   {
        return $this->createQueryBuilder('a')
        ->select('a.remarque')
        ->where('a.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getResult()
   ;
   }



    // public function findAllOrderedByInnerJoinDepartement(): array
    // {
    //     return $this->createQueryBuilder('c')
    //      ->select('d.nom')
    //      ->innerJoin(Departement::class, 'd', 'WITH', 'd.id = c.id')
    //      ->getQuery()
    //      ->getResult()
    //     ;
    // }

    // public function findAllOrderedByInnerJoinDepartement(): array
    // {
    //     return $this->createQueryBuilder('c')
    //      ->select('d.nom')
    //      ->innerJoin(Departement::class, 'd', 'WITH', 'd.id = c.id')
    //      ->getQuery()
    //      ->getResult()
    //     ;
    // }
 

//    /**
//     * @return Attribution[] Returns an array of Attribution objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Attribution
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
