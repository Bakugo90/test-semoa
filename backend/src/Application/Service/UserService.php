<?php

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Enum\UserRole;
use App\Infrastructure\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function register(string $email, string $password): User
    {
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new \RuntimeException('Cet email est déjà utilisé');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRole(UserRole::USER);

        $this->userRepository->save($user);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }
}
