<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
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
        $user = $this->getUser();
        if (!$user instanceof User || $user->getProfile() === null) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('post/index.html.twig', [
            'posts' => $this->postRepository->getPostsByProfile($user->getProfile()),
        ]);
    }

    #[Route('/post/create', name: 'app_post_new', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $user->getProfile() === null) {
            throw $this->createAccessDeniedException();
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setProfile($user->getProfile());
            $this->postRepository->savePost($post);

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/{id}/show', name: 'app_post_show', methods: [Request::METHOD_GET])]
    public function show(Post $post): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_new', ['post_id' => $post->getId()]),
        ]);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $commentForm,
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Post $post, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->savePost($post);

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: [Request::METHOD_POST])]
    public function delete(Post $post, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete-post'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $this->postRepository->deletePost($post);
        }

        return $this->redirectToRoute('app_post', [], Response::HTTP_SEE_OTHER);
    }
}
