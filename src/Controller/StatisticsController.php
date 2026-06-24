<?php

namespace App\Controller;

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
    #[Route('/admin/statistics', name: 'app_statistics', methods: ['GET'])]
    public function index(
        CommentRepository $commentRepository,
        PostRepository $postRepository,
        ProfileRepository $profileRepository
    ): Response {
        $averageCommentsPerPost = $commentRepository->getAverageCommentsPerPost();

        return $this->render('statistics/index.html.twig', [
            'commentCount' => $commentRepository->count([]),
            'maxContentComment' => $commentRepository->getCommentWithMaxContent(),
            'topComments' => $commentRepository->getCommentsWithMaxLikesAndDislikes(),
            'postWithMaxComments' => $postRepository->getPostWithMaxComments(),
            'postWithMinComments' => $postRepository->getPostWithMinComments(),
            'postsAboveAverage' => $postRepository->getPostsWithCommentsGreaterThanAverage($averageCommentsPerPost),
            'avgCommentsPerPost' => $averageCommentsPerPost,
            'topProfiles' => $profileRepository->getTopProfilesWithTotalCommentInTheirPosts(5),
            'profilesWithoutComments' => $profileRepository->getProfilesWithPostsAndWithoutComments(),
        ]);
    }
}
