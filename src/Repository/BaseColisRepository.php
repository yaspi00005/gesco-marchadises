<?php

namespace App\Repository;

use App\Entity\BaseColis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BaseColis>
 */
class BaseColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseColis::class);
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




    public function findByFiltre($client)
    {
        return $this->createQueryBuilder('c')
            ->where('c.destinateurs = :user')
            ->setParameter('user', $client)
            ->orderBy('CASE
        WHEN c.statut = \'Arrivé\' THEN 1
         WHEN c.statut = \'En transit\' THEN 2
          WHEN c.statut = \'Expédié\' THEN 3
           WHEN c.statut = \'Retenu en douane\' THEN 4
          WHEN c.statut = \'En préparation\' THEN 5
        WHEN c.statut = \'Livré\' THEN 6 
        ELSE 7
    END', 'ASC')
            ->setMaxResults(30)
            ->getQuery()->getResult();
    }

    //    public function findOneBySomeField($value): ?BaseColis
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
