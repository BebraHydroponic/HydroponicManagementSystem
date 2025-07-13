<?php
namespace App\Controller;

use App\Service\AuthService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[Route('/api/user')]
class UserController extends AbstractController
{
    public function __construct(
        private AuthService $auth,
        private UserService $usersvc
    ) {}

    #[Route('/login', name: 'user_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $token = $this->auth->login($data['username'] ?? '', $data['password'] ?? '');
        } catch (AuthenticationException $e) {
            return $this->json(['error'=>$e->getMessage()], 401);
        }
        return $this->json(['token'=>$token]);
    }

    #[Route('/refresh', name: 'user_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $token = $this->auth->refreshToken($data['token'] ?? '');
        } catch (AuthenticationException $e) {
            return $this->json(['error'=>$e->getMessage()], 401);
        }
        return $this->json(['token'=>$token]);
    }

    #[Route('/logout', name: 'user_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if ($user) {
            $this->auth->logout($user);
        }
        return new JsonResponse(null, 204);
    }

    #[Route('', name: 'user_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $user = $this->usersvc->createUser($data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json($user, 201);
    }

    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $user = $this->usersvc->updateUser($id, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json($user);
    }

    #[Route('/{id}/lock', name: 'user_lock', methods: ['POST'])]
    public function lockUnlockUser(int $id, Request $request): JsonResponse
    {
        $lock = (bool) json_decode($request->getContent(), true)['lock'];
        try {
            $this->usersvc->lockUnlockUser($id, $lock);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/reset-password', name: 'user_reset_password', methods: ['POST'])]
    public function resetPassword(int $id): JsonResponse
    {
        try {
            $new = $this->usersvc->resetPassword($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error'=>$e->getMessage()], 400);
        }
        return $this->json(['newPassword'=>$new]);
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $users = $this->usersvc->listUsers();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'username' => $u->getUsername(),
            'email' => $u->getEmail(),
            'locked' => $u->isLocked(),
        ], $users);
        return $this->json($data);
    }

    #[Route('/{id}', name: 'user_get', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->usersvc->getUser($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'locked' => $user->isLocked(),
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        // Placeholder for future RBAC checks
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        try {
            $this->usersvc->deleteUser($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(null, 204);
    }
}
