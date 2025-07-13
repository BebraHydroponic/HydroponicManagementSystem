<?php
namespace App\Service;

use App\Entity\AppUser;
use App\Repository\AppUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService
{
    private EntityManagerInterface $em;
    private AppUserRepository $repo;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        AppUserRepository $repo,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->repo = $repo;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function createUser(array $data): AppUser
    {
        $user = new AppUser();
        $user->setUsername($data['username'] ?? '')
             ->setPassword(password_hash($data['password'] ?? '', PASSWORD_BCRYPT))
             ->setEmail($data['email'] ?? null)
             ->setPhone($data['phone'] ?? null)
             ->setDescription($data['description'] ?? null)
             ->setLocked(false);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $this->logger->error('User validation failed', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('User validation failed: ' . (string)$errors);
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->dispatcher->dispatch(new class($user) {
            private AppUser $user;
            public function __construct(AppUser $user) { $this->user = $user; }
            public function getUser(): AppUser { return $this->user; }
        }, 'user.created');

        return $user;
    }

    public function updateUser(int $id, array $data): AppUser
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }
        if (isset($data['description'])) {
            $user->setDescription($data['description']);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $this->logger->error('User validation failed on update', ['errors' => (string)$errors]);
            throw new \InvalidArgumentException('User validation failed: ' . (string)$errors);
        }

        $this->em->flush();

        return $user;
    }

    public function lockUnlockUser(int $id, bool $lock): void
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $user->setLocked($lock);
        $this->em->flush();
    }

    public function resetPassword(int $id): string
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $newPass = bin2hex(random_bytes(5));
        $user->setPassword(password_hash($newPass, PASSWORD_BCRYPT));
        $this->em->flush();

        return $newPass;
    }

    /**
     * Remove a user from the system.
     *
     * @throws \InvalidArgumentException when the user does not exist.
     */
    public function deleteUser(int $id): void
    {
        $user = $this->repo->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Retrieve all users.
     *
     * @return AppUser[]
     */
    public function listUsers(): array
    {
        return $this->repo->findAll();
    }

    /**
     * Get a single user by id.
     */
    public function getUser(int $id): ?AppUser
    {
        return $this->repo->find($id);
    }
}
