<?php

namespace App\Repository;

use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Product;
use App\Entity\User;
use App\Model\SearchDataAttribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findAllOrderedByAttributionDateTime(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.dateAttribution', 'ASC')
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
    if(!empty(($searchDataAttribution->nom))){
        $collaborateurRepository = $collaborateurRepository
        ->andWhere('a.nom LIKE :nom' )
        ->setParameter('nom', "%$searchDataAttribution->nom%")
        ->orderBy('a.id', 'ASC');
    }
    $collaborateurRepository = $collaborateurRepository
    ->select('c.nom')
    ->innerJoin(Collaborateur::class, 'c', 'WITH', 'c.id = a.id');
     //dd($productRepository->getQuery()->getDQL());

    return $collaborateurRepository->getQuery()
        ->getResult();;

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
