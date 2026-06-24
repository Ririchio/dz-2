<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getCurrentUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/new', name: 'app_profile_new', methods: [Request::METHOD_GET, 'POST'])]
    public function create(Request $request): Response
    {
        $profile = new Profile();
        $form = $this->createForm(ProfileType::class, $profile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $profile->setUser($this->getCurrentUser());
            $this->entityManager->persist($profile);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/new.html.twig', ['form' => $form]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request): Response
    {
        $user = $this->getCurrentUser();
        $profile = $user->getProfile();

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', ['form' => $form]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: [Request::METHOD_POST])]
    public function deleteProfile(Request $request): Response
    {
        $user = $this->getCurrentUser();
        $profile = $user->getProfile();

        if ($profile !== null && $this->isCsrfTokenValid('delete-profile', $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($profile);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_profile');
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
