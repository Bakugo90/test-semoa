<?php

namespace App\Controller;

use App\Application\Service\UserService;
use App\DTO\ApiResponseDTO;
use App\DTO\LoginDTO;
use App\DTO\UserRegisterDTO;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            UserRegisterDTO::class,
            'json'
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                ApiResponseDTO::error('Erreur de validation', ['errors' => (string) $errors]),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $user = $this->userService->register($dto->email, $dto->password);
            $token = $this->jwtManager->create($user);

            return $this->json(
                ApiResponseDTO::success([
                    'user' => [
                        'id' => $user->getId()->toRfc4122(),
                        'email' => $user->getEmail(),
                        'role' => $user->getRole()->value
                    ],
                    'token' => $token
                ]),
                Response::HTTP_CREATED
            );
        } catch (\RuntimeException $e) {
            return $this->json(
                ApiResponseDTO::error($e->getMessage()),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            LoginDTO::class,
            'json'
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                ApiResponseDTO::error('Erreur de validation', ['errors' => (string) $errors]),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userService->findByEmail($dto->email);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return $this->json(
                ApiResponseDTO::error('Identifiants invalides'),
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = $this->jwtManager->create($user);

        return $this->json(
            ApiResponseDTO::success([
                'user' => [
                    'id' => $user->getId()->toRfc4122(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()->value
                ],
                'token' => $token
            ])
        );
    }
}
