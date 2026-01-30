<?php

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\DTO\RegisterDTO;
use App\DTO\LoginDTO;
use App\Infrastructure\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function register(RegisterDTO $dto): User
    {
        $existingUser = $this->userRepository->findByEmail($dto->email);
        if ($existingUser) {
            throw new \RuntimeException('Email already exists');
        }

        $user = new User();
        $user->setEmail($dto->email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $user;
    }

    public function login(LoginDTO $dto): ?User
    {
        $user = $this->userRepository->findByEmail($dto->email);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return null;
        }

        return $user;
    }
}
