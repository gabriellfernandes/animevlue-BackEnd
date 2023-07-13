<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Middleware\MiddlewareEmail;
use App\Repository\AnimeRepository;
use App\Repository\FavoriteRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends AbstractController
{
    private $middlewareEmail;

    public function __construct(MiddlewareEmail $middlewareEmail)
    {
        $this->middlewareEmail = $middlewareEmail;
    }

    #[Route('/favorite', name: 'favorite_list', methods: ['GET'])]
    public function index(FavoriteRepository $favoriteRepository): JsonResponse
    {
        return $this->json([
            'data' => $favoriteRepository->findAll()
        ], 200, [], ['groups' => "favorite_show"]);
    }

    #[Route('/favorite/user', name: 'favorite_listByUser', methods: ['GET'])]
    public function getAllByUser(Request $request,  FavoriteRepository $favoriteRepository, UserRepository $userRepository): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $emailValidationResult = $this->middlewareEmail->validateEmailToken($authorizationHeader);

        $user = $userRepository->findOneBy(["email" => $emailValidationResult['email']]);

        if (!$user) {
            return $this->json([
                'message' => 'user not found!'
            ], 404);
        }

        $favorite = $favoriteRepository->findBy(["user" => $user]);
        if (!$favorite) {
            return $this->json([
                'message' => 'favorite not found!',
            ], 404);
        }

        return $this->json([
            'data' => $favorite
        ], 200, [], ['groups' => "favorite_show"]);
    }

    #[Route('/favorite/{id}', name: 'favorite_s', methods: ['GET'])]
    public function single($id, FavoriteRepository $favoriteRepository): JsonResponse
    {
        $favorite = $favoriteRepository->find($id);

        if (!$favorite) {
            return $this->json([
                'message' => 'favorite not found!',
            ], 404);
        }

        return $this->json([
            'data' => $favorite
        ], 200, [], ['groups' => "favorite_show"]);
    }


    #[Route('/favorite', name: 'favorite_create', methods: ['POST'])]
    public function create(Request $request,  FavoriteRepository $favoriteRepository, UserRepository $userRepository, AnimeRepository $animeRepository, ManagerRegistry $doctrine): JsonResponse
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

        $favorite = $favoriteRepository->findOneBy(['user' => $user, 'anime' => $anime]);

        if (!$favorite) {
            $newFavorite = new Favorite();
            $newFavorite->setFavorite(true);
            $newFavorite->setAnime($anime);
            $newFavorite->setUser($user);
            $favoriteRepository->save($newFavorite, true);
            return $this->json([
                'message' => "favorite created success!",
                'data' => $newFavorite
            ], 201, [], ['groups' => "favorite_show"]);
        };

        $favorite->setFavorite(!$favorite->isFavorite());
        $doctrine->getManager()->flush();


        return $this->json([
            'message' => 'Favorite update success!',
            'data' => $favorite
        ], 200, [], ['groups' => "favorite_show"]);
    }
}
