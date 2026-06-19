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
     * Возвращает комментарий с максимальным содержимым (content)
     */
    public function getCommentWithMaxContent()
    {
        // TODO: Реализовать запрос
    }


    /**
     * Возвращает топ-5 комментариев с максиамальным суммарным количеством лайков и дизлайков
     * ПОДСКАЗКА: нужно добавить в сущность комментария поля с лайками и дизлайками, не забудьте добавить возможность в UI лайкать и дизлайкать комменты
     */
    public function getCommentsWithMaxLikesAndDislikes(int $maxTop = 5)
    {
        // TODO: Реализовать запрос
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
