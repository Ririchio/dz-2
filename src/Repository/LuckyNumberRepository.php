<?php

namespace App\Repository;

use App\Entity\LuckyNumber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LuckyNumberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LuckyNumber::class);
    }

    public function saveLuckyNumber(int $number): void
    {
        $luckyNumber = new LuckyNumber();
        $luckyNumber->setValue($number);

        $em = $this->getEntityManager();
        $em->persist($luckyNumber);
        $em->flush();
    }

    public function findAllOdds(int $maxValue = 50): array
    {
        $qb = $this->createQueryBuilder('l');

        $qb->andWhere('MOD(l.value, 2) = 1', 'l.value < :maxVal');
        $qb->setParameter('maxVal', $maxValue);

        $qb->orderBy('l.value', 'DESC');
        $qb->setMaxResults(5);

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
}
