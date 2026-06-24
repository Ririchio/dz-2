<?php

namespace App\Repository;

use App\Entity\CommentReaction;
use App\Entity\Post;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentReaction::class);
    }

    public function getTypesForPostAndProfile(Post $post, Profile $profile): array
    {
        $rows = $this->createQueryBuilder('reaction')
            ->select('IDENTITY(reaction.comment) AS commentId, reaction.type AS type')
            ->innerJoin('reaction.comment', 'comment')
            ->andWhere('comment.post = :post')
            ->andWhere('reaction.profile = :profile')
            ->setParameter('post', $post)
            ->setParameter('profile', $profile)
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['commentId']] = $row['type'];
        }

        return $result;
    }
}
