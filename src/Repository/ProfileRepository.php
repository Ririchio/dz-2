<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function getTopProfilesWithTotalCommentInTheirPosts(int $topMax = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id AS profileId')
            ->addSelect('u.email AS email')
            ->addSelect('p.bio AS bio')
            ->addSelect('COUNT(c.id) AS commentsCount')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.posts', 'post')
            ->leftJoin('post.comments', 'c')
            ->groupBy('p.id')
            ->addGroupBy('u.email')
            ->addGroupBy('p.bio')
            ->orderBy('commentsCount', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults($topMax)
            ->getQuery()
            ->getArrayResult();
    }

    public function getProfilesWithPostsAndWithoudComments(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id AS profileId')
            ->addSelect('u.email AS email')
            ->addSelect('p.bio AS bio')
            ->addSelect('COUNT(DISTINCT post.id) AS postsCount')
            ->innerJoin('p.posts', 'post')
            ->leftJoin('p.user', 'u')
            ->leftJoin(Comment::class, 'c', Join::WITH, 'c.author = p')
            ->andWhere('c.id IS NULL')
            ->groupBy('p.id')
            ->addGroupBy('u.email')
            ->addGroupBy('p.bio')
            ->orderBy('postsCount', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
