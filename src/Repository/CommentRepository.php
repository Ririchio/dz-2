<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Возвращает комментарий с максимальным содержимым (content).
     */
    public function getCommentWithMaxContent(): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->orderBy('LENGTH(c.content)', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает топ комментариев с максимальным суммарным количеством положительных и отрицательных реакций.
     *
     * @return Comment[]
     */
    public function getCommentsWithMaxLikesAndDislikes(int $maxTop = 5): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('(c.positiveVotes + c.negativeVotes) AS HIDDEN reactionsCount')
            ->orderBy('reactionsCount', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults($maxTop)
            ->getQuery()
            ->getResult();
    }
}
