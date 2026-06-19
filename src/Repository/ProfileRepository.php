<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profile>
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * Получить топ-5 профилей, чьи посты имеют максимальное суммарное количество комментариев
     */
    public function getTopProfilesWithTotalCommentInTheirPosts(int $topMax = 5)
    {
        // TODO: Реализовтать метод
    }

    /** 
     * Получить профили, которые не оставили ни одного комментария, но имют хотя бы один пост
     */
    public function getProfilesWithPostsAndWithoudComments()
    {
        // TODO: Реализовать метод
    }

    //    /**
    //     * @return Profile[] Returns an array of Profile objects
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

    //    public function findOneBySomeField($value): ?Profile
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
