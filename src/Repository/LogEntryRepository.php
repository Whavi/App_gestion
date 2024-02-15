<?php

namespace App\Repository;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogEntry>
 *
 * @method LogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogEntry[]    findAll()
 * @method LogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function findAllOrderedByLogNumber(): array
   {
       return $this->createQueryBuilder('l')
           ->orderBy('l.id', 'DESC')
           ->getQuery()
           ->getResult()
       ;
   }
   public function filterByLevelsAndCategories($levels, $categories, $createdAt)
{
    $queryBuilder = $this->createQueryBuilder('l')
        ->orderBy('l.id', 'DESC');
    if ($levels !== null) {
        $queryBuilder
            ->andWhere('l.level IN (:levels)')
            ->setParameter('levels', $levels);
    }
    if ($categories !== null) {
        $queryBuilder
            ->andWhere('l.channel IN (:channels)')
            ->setParameter('channels', $categories);
    }
    if ($createdAt !== null) {
        $queryBuilder->andWhere('l.createdAt = :createdAt')
            ->setParameter('createdAt', $createdAt);
    }
    return $queryBuilder->getQuery()->getResult();
}

//    /**
//     * @return LogEntry[] Returns an array of LogEntry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LogEntry
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
