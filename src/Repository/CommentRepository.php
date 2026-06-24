<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentWithMaxContent(): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->orderBy('LENGTH(c.content)', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCommentsWithMaxLikesAndDislikes(int $maxTop = 5): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('(c.likes + c.dislikes) AS HIDDEN reactionsCount')
            ->orderBy('reactionsCount', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults($maxTop)
            ->getQuery()
            ->getResult();
    }

    public function getAverageCommentsPerPost(): float
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(comment.id) AS commentsCount, COUNT(DISTINCT post.id) AS postsCount')
            ->from(Post::class, 'post')
            ->leftJoin('post.comments', 'comment')
            ->getQuery()
            ->getSingleResult();

        if ((int) $result['postsCount'] === 0) {
            return 0.0;
        }

        return (int) $result['commentsCount'] / (int) $result['postsCount'];
    }
}
