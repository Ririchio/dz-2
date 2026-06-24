<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPostsByProfile(Profile $profile): array
    {
        return $this->createQueryBuilder('post')
            ->addSelect('comments')
            ->leftJoin('post.comments', 'comments')
            ->andWhere('post.profile = :profile')
            ->setParameter('profile', $profile)
            ->orderBy('post.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getFeed(): array
    {
        return $this->createQueryBuilder('post')
            ->addSelect('profile', 'user', 'comments')
            ->innerJoin('post.profile', 'profile')
            ->innerJoin('profile.user', 'user')
            ->leftJoin('post.comments', 'comments')
            ->orderBy('post.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function savePost(Post $post): void
    {
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();
    }

    public function deletePost(Post $post): void
    {
        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
    }

    public function getPostWithMaxComments(): ?Post
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->orderBy('commentsCount', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPostWithMinComments(): ?Post
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->innerJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->orderBy('commentsCount', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPostsWithCommentsGreaterThanAverage(float $average): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->having('COUNT(c.id) > :average')
            ->setParameter('average', (int) floor($average))
            ->orderBy('commentsCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
