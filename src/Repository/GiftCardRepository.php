<?php

namespace App\Repository;

use App\Entity\GiftCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GiftCard>
 */
class GiftCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GiftCard::class);
    }

    public function findAvailableGiftCards(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.isRedeemed = :isRedeemed')
            ->setParameter('isRedeemed', false)
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?GiftCard
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
