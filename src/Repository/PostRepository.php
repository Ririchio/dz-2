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
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere('p.profile = :profile');
        $qb->setParameter('profile', $profile);

        $query = $qb->getQuery();
        return $query->getArrayResult();
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

    public function getPostWithMaxComments()
    {
        $qb = $this->createQueryBuilder('p'); // Создаем строителя запросов
        $qb->select('p'); // Выполняем выборку поста с псевдонимом 'p'
        $qb->addSelect('COUNT(c.id) AS HIDDEN cnt'); // Добавляем еще одну "скрытую выборку"
        $qb->leftJoin('p.comments', 'c', 'p.id = c.post_id'); // Делаем LEFT JOIN с таблицей comments
        $qb->groupBy('p.id'); // Группируем по айди поста
        $qb->orderBy('cnt', 'DESC'); // Сортируем результат по скрытой выборке по убыванию
        $qb->setMaxResults(1); // Устанавлием один результат выборки
        return $qb->getQuery()->getOneOrNullResult(); // Получаем и выполняем запрос, возвращаем один результат или null
    }


    /**
     * Возвращается пост с минимальным (но не нулевым) количеством комментариев
     */
    public function getPostWithMinComments()
    {
        // TODO: Реализовать метод
    }

    /**
     * Возвращает список постов, у которых комментариев больше среднего
     * ПОДСКАЗКА: Среднее количество комментариев можно выполнить отдельным запросом или подзапросом
     */
    public function getPostsWithCommentsGreaterThanAverage()
    {
        // TODO: Реализовать метод
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
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

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
