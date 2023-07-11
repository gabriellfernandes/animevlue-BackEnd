<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    #[Route('/user', name: 'user_byId', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $emailValidationResult = $this->validateEmailToken($authorizationHeader);

        return $this->json([
            'data' => $userRepository->findOneBy(['email' => $emailValidationResult['email']])
        ], 200, [], ['groups' => 'user_show']);
    }

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $generatePasswordHash): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

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
            'data' =>  $user,
        ], 201, [],  ['groups' => 'user_show']);
    }

    #[Route('user/{id}', name: 'user_update', methods: ['PUT', 'PATCH'])]
    public function update($id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $generatePasswordHash, ManagerRegistry $doctrine): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $authorizationHeader = $request->headers->get('Authorization');


        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'message' => 'user not found!',
            ], 404);
        }

        $emailValidationResult = $this->validateEmailToken($authorizationHeader, $user);

        if (!$emailValidationResult['valid']) {
            return $this->json([
                'message' => 'user not found!',
            ], 404);
        }


        if (array_key_exists('username', $data)) $user->setNickName($data['username']);
        if (array_key_exists('email', $data)) $user->setEmail($data['email']);
        if (array_key_exists('password', $data)) $user->setPassword($generatePasswordHash->hashPassword($user, $data['password']));
        $user->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $doctrine->getManager()->flush();

        return $this->json([
            'message' => 'user updated success!',
            'data' =>  $user,
        ], 200, [], ['groups' => 'user_show']);
    }

    #[Route('user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete($id, Request $request, UserRepository $userRepository): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $user = $userRepository->find($id);
        $emailValidationResult = $this->validateEmailToken($authorizationHeader, $user);

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

    private function validateEmailToken($authorizationHeader, User $entity = new User()): array
    {
        if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
            $jwtUserToken = new JWTUserToken();
            $jwtUserToken->setRawToken($token);
            $decodedToken = $this->jwtManager->decode($jwtUserToken);
            $emailToken = $decodedToken['username'];
        }

        return ['valid' => $entity->getEmail() == $emailToken, 'email' => $emailToken];
    }
}
