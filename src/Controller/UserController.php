<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Middleware\MiddlewareEmail;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $middlewareEmail;

    public function __construct(MiddlewareEmail $middlewareEmail)
    {
        $this->middlewareEmail = $middlewareEmail;
    }

    #[Route('/user', name: 'user_byId', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader);

        return $this->json($userRepository->findOneBy(['email' => $emailValidationResult['email']]), 200, [], ['groups' => 'user_show']);
    }

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $generatePasswordHash): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        if (!array_key_exists('username', $data)) return $this->json(['message' => 'the username field is missing'], 404);
        if (!array_key_exists('email', $data)) return $this->json(['message' => 'the email field is missing'], 404);
        if (!array_key_exists('password', $data)) return $this->json(['message' => 'the password field is missing'], 404);

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'message' => 'email already in use',
            ], 409);
        }

        $user = new User;
        $user->setNickName($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($generatePasswordHash->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $user->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $userRepository->save($user, true);


        return $this->json([
            'message' => 'user created success!',
            'data' => $user,
        ], 201, [],  ['groups' => 'user_show']);
    }

    #[Route('/user', name: 'user_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $generatePasswordHash, ManagerRegistry $doctrine): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $authorizationHeader = $request->headers->get('Authorization');
        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader);
        $user = $userRepository->findOneBy(["email" => $emailValidationResult['email']]);

        if (!$user) {
            return $this->json([
                'message' => 'user not found!'
            ], 404);
        }

        if (array_key_exists('username', $data)) $user->setNickName($data['username']);
        if (array_key_exists('email', $data)) $user->setEmail($data['email']);
        if (array_key_exists('password', $data)) $user->setPassword($generatePasswordHash->hashPassword($user, $data['password']));
        $user->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $doctrine->getManager()->flush();

        return $this->json([
            'message' => 'user updated success!',
            'data' => $user,
        ], 200, [], ['groups' => 'user_show']);
    }

    #[Route('user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete($id, Request $request, UserRepository $userRepository): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $user = $userRepository->find($id);
        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader, $user);

        if (!$user) {
            return $this->json([
                'message' => 'user not found!',
            ], 404);
        }

        if ($emailValidationResult['valid'] || 'ROLE_ADMIN' == $user->getRoles()[0]) {
            $userRepository->remove($user, true);
            return $this->json([], 204);
        } else {
            return $this->json(['message' => 'user not found!'], 409);
        }
    }
}
