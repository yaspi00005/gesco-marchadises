<?php

namespace App\Repository;

use App\Entity\Expeditions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expeditions>
 */
class ExpeditionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expeditions::class);
    }


    
   /**
    * @return Arrivages[] Returns an array of Arrivages objects
    */
    public function findBydate($debut,$fin): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('DATE(a.dateExpeditions) BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }






    //    /**
    //     * @return Expeditions[] Returns an array of Expeditions objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Expeditions
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
