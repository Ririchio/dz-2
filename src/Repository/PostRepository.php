<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPostsByProfile(Profile $profile): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.profile = :profile')
            ->setParameter('profile', $profile)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function savePost(Post $post): void
    {
        $em = $this->getEntityManager();
        $em->persist($post);
        $em->flush();
    }

    public function deletePost(Post $post): void
    {
        $em = $this->getEntityManager();
        $em->remove($post);
        $em->flush();
    }

    public function getPostWithMaxComments(): ?Post
    {
        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->orderBy('commentsCount', 'DESC')
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
            ->having('COUNT(c.id) > 0')
            ->orderBy('commentsCount', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPostsWithCommentsGreaterThanAverage(): array
    {
        $commentCounters = $this->createQueryBuilder('p')
            ->select('COUNT(c.id) AS commentsCount')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->getQuery()
            ->getScalarResult();

        if ($commentCounters === []) {
            return [];
        }

        $totalComments = array_sum(array_map(static fn (array $row): int => (int) $row['commentsCount'], $commentCounters));
        $averageComments = $totalComments / count($commentCounters);

        return $this->createQueryBuilder('p')
            ->addSelect('COUNT(c.id) AS HIDDEN commentsCount')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->having('COUNT(c.id) > :averageComments')
            ->setParameter('averageComments', $averageComments)
            ->orderBy('commentsCount', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
