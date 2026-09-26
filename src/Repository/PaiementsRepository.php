<?php

namespace App\Repository;

use App\Entity\Paiements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiements>
 */
class PaiementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiements::class);
    }

    public function getMontantTotalPaye( $colis): float
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.montants)')
            ->where('p.colis = :colis')
            ->setParameter('colis', $colis)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }


    public function findByExpedition($expeditionId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.colis', 'c')  // Relation entre Paiements et BaseColis
            ->join('c.expeditions', 'e') // Relation entre BaseColis et Expeditions
            ->where('e.id = :expeditionId')
            ->setParameter('expeditionId', $expeditionId)
            ->getQuery()
            ->getResult();
    }




    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.datePaiements BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Paiements[] Returns an array of Paiements objects
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

    //    public function findOneBySomeField($value): ?Paiements
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
