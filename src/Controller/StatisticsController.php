<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'app_statistics')]
    public function index(PostRepository $postRepository): Response
    {
        /** 
         * @var array<Post> $allPosts 
         * TODO: Решить проблему N + 1
        */
        $allPosts = $postRepository->findAll();
        $commentsCount = 0;
        foreach ($allPosts as $post) {
            $commentsCount += $post->getComments()->count();
        }

        $maxCommentsPost = $postRepository->getPostWithMaxComments();

        return $this->render('statistics/index.html.twig', [
            'commentsCount' => $commentsCount,
            'maxCommentsPost' => $maxCommentsPost
        ]);
    }
}
