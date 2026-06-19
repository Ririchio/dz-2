<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
    ) {
    }

    #[Route('/registration', name: 'app_registration')]
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $plainPassword = $request->getPayload()->get('_password');
            $email = $request->getPayload()->get('_email');

            if ($plainPassword === null || $email === null) {
                throw new BadRequestHttpException('Email or password not in form');
            }

            $user = new User();
            $user->setEmail($email);

            $defaultRole = $this->roleRepository->findOneBy(['name' => 'ROLE_USER']);
            if ($defaultRole !== null) {
                $user->addRole($defaultRole);
            }

            $hashedPassword = $this->hasher->hashPassword($user, $plainPassword);
            $this->userRepository->upgradePassword($user, $hashedPassword);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', []);
    }
}
