<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/post/{post_id:post.id}/comment')]
final class CommentController extends AbstractController
{
    #[Route('/new', name: 'app_comment_new', methods: ['POST'])]
    public function new(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User || $user->getProfile() === null) {
                throw $this->createAccessDeniedException();
            }

            $comment->setAuthor($user->getProfile());
            $comment->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/positive', name: 'app_comment_positive', methods: ['POST'])]
    public function positive(Post $post, Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkPost($post, $comment);

        if ($this->isCsrfTokenValid('positive'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $comment->addPositiveVote();
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/negative', name: 'app_comment_negative', methods: ['POST'])]
    public function negative(Post $post, Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkPost($post, $comment);

        if ($this->isCsrfTokenValid('negative'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $comment->addNegativeVote();
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Post $post, Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkPost($post, $comment);

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    private function checkPost(Post $post, Comment $comment): void
    {
        if ($comment->getPost()?->getId() !== $post->getId()) {
            throw $this->createNotFoundException();
        }
    }
}
