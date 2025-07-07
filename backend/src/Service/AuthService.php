<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Repository\AppUserRepository;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
    private AppUserRepository $userRepo;
    private string $jwtSecret;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        AppUserRepository $userRepo,
        string $jwtSecret,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->userRepo = $userRepo;
        $this->jwtSecret = $jwtSecret;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function login(string $username, string $password): string
    {
        $user = $this->userRepo->findOneBy(['username' => $username]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $this->logger->warning('Failed login attempt', ['username' => $username]);
            throw new AuthenticationException('Invalid username or password.');
        }

        if ($user->isLocked()) {
            throw new AuthenticationException('User account is locked.');
        }

        $this->dispatcher->dispatch(new class($user) {
            private AppUser $user;
            public function __construct(AppUser $user) { $this->user = $user; }
            public function getUser(): AppUser { return $this->user; }
        }, 'user.login_success');

        return $this->generateJWT($user);
    }

public function refreshToken(string $token): string
{
    try {
        $payload = JWT::decode($token, $this->jwtSecret, ['HS256']);
        // $payload is a stdClass object, not an array!
        $user = $this->userRepo->find($payload->uid);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        return $this->generateJWT($user);
    } catch (\Exception $e) {
        $this->logger->warning('Invalid JWT token refresh attempt', ['error' => $e->getMessage()]);
        throw new AuthenticationException('Invalid token.');
    }
}

    public function logout(AppUser $user): void
    {
        $this->dispatcher->dispatch(new class($user) {
            private AppUser $user;
            public function __construct(AppUser $user) { $this->user = $user; }
            public function getUser(): AppUser { return $this->user; }
        }, 'user.logout');
    }

    private function generateJWT(AppUser $user): string
    {
        $payload = [
            'uid' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
