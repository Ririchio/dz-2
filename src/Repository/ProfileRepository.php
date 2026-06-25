<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function getTopProfilesWithTotalCommentInTheirPosts(int $topMax = 5): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('user')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->innerJoin('p.user', 'user')
            ->leftJoin('p.posts', 'post')
            ->leftJoin('post.comments', 'c')
            ->groupBy('p.id')
            ->addGroupBy('user.id')
            ->orderBy('commentsCount', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults($topMax)
            ->getQuery()
            ->getResult();
    }

    public function getProfilesWithPostsAndWithoutComments(): array
    {
        return $this->createQueryBuilder('p')
            ->distinct()
            ->addSelect('user')
            ->innerJoin('p.user', 'user')
            ->innerJoin('p.posts', 'post')
            ->leftJoin(Comment::class, 'c', 'WITH', 'c.author = p')
            ->andWhere('c.id IS NULL')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
