<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Middleware\MiddlewareEmail;
use App\Repository\AnimeRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    private $middlewareEmail;

    public function __construct(MiddlewareEmail $middlewareEmail)
    {
        $this->middlewareEmail = $middlewareEmail;
    }

    #[Route('/comments', name: 'comments_list', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        return $this->json([
            'data' => $commentRepository->findAll(),
        ], 200, [], ['groups' => 'comment_show']);
    }

    #[Route('/comments/{id}', name: 'comments_list', methods: ['GET'])]
    public function getById($id, CommentRepository $commentRepository): JsonResponse
    {

        $comment = $commentRepository->find($id);

        if (!$comment) {
            return $this->json([
                'message' => "comment not found!",
            ], 404, []);
        }

        return $this->json([
            'data' => $comment,
        ], 200, [], ['groups' => 'comment_show']);
    }

    #[Route('/comments', name: 'comments_create', methods: ['POST'])]
    public function create(Request $request, CommentRepository $commentRepository, UserRepository $userRepository, AnimeRepository $animeRepository): JsonResponse
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

        $anime = $animeRepository->findOneBy(['anime_id' => $data['anime_id']]);
        if (!$anime) {
            return $this->json([
                'message' => 'anime not found!'
            ], 404);
        }

        $comment = new Comment();
        $comment->setComment($data['comment']);
        $comment->setUser($user);
        $comment->setAnime($anime);
        $comment->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $comment->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $commentRepository->save($comment, true);

        return $this->json([
            'message' => "comment created success!",
            'data' =>  $comment
        ], 201, [], ['groups' => 'comment_show']);
    }

    #[Route('/comments/{id}', name: 'comments_update', methods: ['PUT', 'PATCH'])]
    public function update($id, Request $request, CommentRepository $commentRepository, ManagerRegistry $doctrine): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }


        $comment = $commentRepository->find($id);

        if (!$comment) {
            return $this->json([
                'message' => "comment not found!",
            ], 404, []);
        }

        $authorizationHeader = $request->headers->get('Authorization');

        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader, $comment->getUser());

        if (!$emailValidationResult['valid']) {
            return $this->json([
                'message' => "not authorized!",
            ], 401, []);
        }

        $comment->setComment($data['comment']);
        $comment->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $doctrine->getManager()->flush();

        return $this->json([
            'message' => "comment update success!",
            'data' =>  $comment
        ], 201, [], ['groups' => 'comment_show']);
    }

    #[Route('/comments/{id}', name: 'comments_delete', methods: ['DELETE'])]
    public function delete($id, Request $request, CommentRepository $commentRepository): JsonResponse
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            return $this->json([
                'message' => "comment not found!",
            ], 404, []);
        }

        $authorizationHeader = $request->headers->get('Authorization');

        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader, $comment->getUser());

        if (!$emailValidationResult['valid']) {
            return $this->json([
                'message' => "not authorized!",
            ], 401, []);
        }

        $commentRepository->remove($comment, true);

        return $this->json([], 204, [], []);
    }
}
