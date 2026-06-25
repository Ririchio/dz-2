<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentReactionRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PostController extends AbstractController
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    #[Route('/post', name: 'app_post', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $this->postRepository->getFeed(),
            'mine' => false,
        ]);
    }

    #[Route('/post/mine', name: 'app_post_mine', methods: [Request::METHOD_GET])]
    public function mine(): Response
    {
        $user = $this->getCurrentUser();
        $profile = $user->getProfile();

        return $this->render('post/index.html.twig', [
            'posts' => $profile === null ? [] : $this->postRepository->getPostsByProfile($profile),
            'mine' => true,
        ]);
    }

    #[Route('/post/create', name: 'app_post_new', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function createPost(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getCurrentUser();
            $profile = $user->getProfile();
            if ($profile === null) {
                $this->addFlash('error', 'Сначала создайте профиль.');

                return $this->redirectToRoute('app_profile');
            }
            $post->setProfile($profile);

            $this->postRepository->savePost($post);

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/new.html.twig', ['form' => $form]);
    }

    #[Route('/post/{id}/show', name: 'app_post_show', methods: [Request::METHOD_GET])]
    public function showPost(Post $post, CommentReactionRepository $reactionRepository): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_new', ['post_id' => $post->getId()]),
        ]);

        $user = $this->getCurrentUser();
        $profile = $user->getProfile();
        $userReactions = $profile === null
            ? []
            : $reactionRepository->getTypesForPostAndProfile($post, $profile);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $commentForm,
            'userReactions' => $userReactions,
            'canInteract' => $profile !== null,
            'canManage' => $this->canManagePost($post),
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function editPost(Post $post, Request $request): Response
    {
        if (!$this->canManagePost($post)) {
            throw $this->createAccessDeniedException('Редактировать этот пост может только его автор или администратор.');
        }

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->savePost($post);

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', ['form' => $form]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: [Request::METHOD_POST])]
    public function deletePost(Post $post, Request $request): Response
    {
        if (!$this->canManagePost($post)) {
            throw $this->createAccessDeniedException('Удалить этот пост может только его автор или администратор.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $this->postRepository->deletePost($post);
        }

        return $this->redirectToRoute('app_post');
    }

    private function canManagePost(Post $post): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $profile = $post->getProfile();
        if ($profile === null) {
            return false;
        }

        return $profile->getUser() === $this->getCurrentUser();
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
