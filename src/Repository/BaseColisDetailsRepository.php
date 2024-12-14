<?php

namespace App\Repository;

use App\Entity\BaseColisDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BaseColisDetails>
 */
class BaseColisDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseColisDetails::class);
    }

    public function countByColi($mois, $annee)
    {
        $query = $this->createQueryBuilder('b');
        $query->select('count(b)');

        if ($mois != null) {
            $query->Where('MONTH(b.dateReceptions) = :mois')
                ->setParameter('mois', $mois)
                ->andWhere('YEAR(b.dateReceptions) = :annee')
                ->setParameter('annee', $annee);
        }
        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return BaseColisDetails[] Returns an array of BaseColisDetails objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BaseColisDetails
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
