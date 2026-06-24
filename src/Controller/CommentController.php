<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CommentReaction;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post/{post_id:post.id}/comment')]
#[IsGranted('ROLE_USER')]
final class CommentController extends AbstractController
{
    #[Route('/new', name: 'app_comment_new', methods: ['POST'])]
    public function new(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getCurrentUser();
            $profile = $user->getProfile();
            if ($profile === null) {
                $this->addFlash('error', 'Сначала создайте профиль.');

                return $this->redirectToRoute('app_profile');
            }
            $comment->setAuthor($profile);
            $comment->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{comment_id:comment.id}/like', name: 'app_comment_like', methods: ['POST'])]
    public function like(
        Post $post,
        Comment $comment,
        Request $request,
        CommentReactionRepository $reactionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('like'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $this->react($post, $comment, CommentReaction::LIKE, $reactionRepository, $entityManager);
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{comment_id:comment.id}/dislike', name: 'app_comment_dislike', methods: ['POST'])]
    public function dislike(
        Post $post,
        Comment $comment,
        Request $request,
        CommentReactionRepository $reactionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('dislike'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $this->react($post, $comment, CommentReaction::DISLIKE, $reactionRepository, $entityManager);
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    private function react(
        Post $post,
        Comment $comment,
        string $type,
        CommentReactionRepository $reactionRepository,
        EntityManagerInterface $entityManager
    ): void {
        if ($comment->getPost() !== $post) {
            return;
        }

        $user = $this->getCurrentUser();
        $profile = $user->getProfile();
        if ($profile === null) {
            return;
        }

        $reaction = $reactionRepository->findOneBy([
            'comment' => $comment,
            'profile' => $profile,
        ]);

        if ($reaction === null) {
            $reaction = (new CommentReaction())
                ->setComment($comment)
                ->setProfile($profile)
                ->setType($type);
            $entityManager->persist($reaction);
            if ($type === CommentReaction::LIKE) {
                $comment->like();
            } else {
                $comment->dislike();
            }
        } elseif ($reaction->getType() === $type) {
            if ($type === CommentReaction::LIKE) {
                $comment->removeLike();
            } else {
                $comment->removeDislike();
            }
            $entityManager->remove($reaction);
        } else {
            if ($reaction->getType() === CommentReaction::LIKE) {
                $comment->removeLike()->dislike();
            } else {
                $comment->removeDislike()->like();
            }
            $reaction->setType($type);
        }

        $entityManager->flush();
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
