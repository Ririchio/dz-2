<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/registration', name: 'app_registration')]
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $plainPassword = $request->getPayload()->get('_password');
            $email = $request->getPayload()->get('_email');

            if (!is_string($plainPassword) || !is_string($email) || $plainPassword === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new BadRequestHttpException('Укажите корректные email и пароль.');
            }

            if ($this->userRepository->loadUserByIdentifier($email) !== null) {
                $this->addFlash('error', 'Пользователь с таким email уже зарегистрирован.');

                return $this->redirectToRoute('app_registration');
            }

            $user = new User();
            $user->setEmail($email);

            $role = $this->roleRepository->findOneBy(['name' => 'ROLE_USER']);
            if ($role === null) {
                $role = (new Role())->setName('ROLE_USER');
                $this->entityManager->persist($role);
            }
            $user->addRole($role);

            $hashedPassword = $this->hasher->hashPassword($user, $plainPassword);
            $this->userRepository->upgradePassword($user, $hashedPassword);

            $this->addFlash('success', 'Регистрация завершена. Теперь войдите с указанными email и паролем.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', []);
    }
}
