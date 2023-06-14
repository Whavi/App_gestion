<?php

namespace App\Repository;

use App\Entity\Attribution;
use App\Entity\Collaborateur;
use App\Entity\Departement;
use App\Model\SearchDataCollaborateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collaborateur>
 *
 * @method Collaborateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collaborateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collaborateur[]    findAll()
 * @method Collaborateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollaborateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collaborateur::class);
    }

    public function save(Collaborateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Collaborateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findAllOrderedByInnerJoinCollaborateurName(): array
   {
       return $this->createQueryBuilder('c')
        ->select("d.nom")
        ->innerJoin(Departement::class, 'd', "WITH", 'c.id = c.departement')
        ->getQuery()
        ->getResult()
       ;
   }
    public function findAllOrderedByCollaborateurNumber(): array
   {
       return $this->createQueryBuilder('c')
           ->orderBy('c.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }
   public function findAllOrderedByInnerJoinDepartement(): array
   {
       return $this->createQueryBuilder('c')
        ->select('d.nom')
        ->innerJoin(Departement::class, 'd', 'WITH', 'd.id = c.id')
        ->getQuery()
        ->getResult()
       ;
   }

   public function findAllOrderedByNameCollaborateur(SearchDataCollaborateur $searchDataCollaborateur)
   {

    $collaborateurRepository = $this->createQueryBuilder('c');

    if(!empty(($searchDataCollaborateur->nom) or ($searchDataCollaborateur->prenom) or ($searchDataCollaborateur->email) or ($searchDataCollaborateur->id))){
        $collaborateurRepository = $collaborateurRepository
        ->andWhere('c.nom LIKE :nom OR c.prenom LIKE :prenom OR c.email LIKE :email OR c.id LIKE :id' )
        ->setParameter('nom', "%$searchDataCollaborateur->nom%")
        ->setParameter('prenom', "%($searchDataCollaborateur->prenom)%")
        ->setParameter('email', "%($searchDataCollaborateur->email)%")
        ->setParameter('id', "%($searchDataCollaborateur->id)%")
        ->orderBy('c.id', 'ASC');
    }
            return $collaborateurRepository->getQuery()
            ->getResult();;

   }

   public function findAllOrderedByInnerJoinAttributionId(): array
   {
       return $this->createQueryBuilder('c')
        ->select('a.id')
        ->innerJoin(Attribution::class, 'a', 'WITH', 'a.id = c.id')
        ->getQuery()
        ->getResult()
       ;
   }


    

//    /**
//     * @return Collaborateur[] Returns an array of Collaborateur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Collaborateur
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
