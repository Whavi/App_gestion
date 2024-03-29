<?php

namespace App\Repository;

use App\Entity\Departement;
use App\Model\SearchDataDepartement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Departement>
 *
 * @method Departement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Departement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Departement[]    findAll()
 * @method Departement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departement::class);
    }

    public function save(Departement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Departement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedByDepartementRank(): array
   {
       return $this->createQueryBuilder('d')
           ->orderBy('d.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

   public function findAllOrderedByDepartementNameASC(): array
   {
       return $this->createQueryBuilder('d')
           ->orderBy('d.nom', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }


   public function findAllOrderedByNameDepartement(SearchDataDepartement $searchDataDepartement)
   {

    $repositoryDepartement = $this->createQueryBuilder('d');

    if(!empty($searchDataDepartement->nom)){
        $repositoryDepartement = $repositoryDepartement
        ->andWhere('d.nom LIKE :nom')
        ->setParameter('nom', "%$searchDataDepartement->nom%")

        ->orderBy('d.id', 'ASC');
    }
            return $repositoryDepartement->getQuery()->getResult();;

   }

//    /**
//     * @return Departement[] Returns an array of Departement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Departement
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
