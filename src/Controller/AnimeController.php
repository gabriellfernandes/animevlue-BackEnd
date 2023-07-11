<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Repository\AnimeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AnimeController extends AbstractController
{
    #[Route('/anime', name: 'anime_list', methods: ["GET"])]
    public function index(AnimeRepository $animeRepository): JsonResponse
    {
        return $this->json([
            'data' => $animeRepository->findAll()
        ]);
    }

    #[Route('/anime', name: 'anime_create', methods: ["POST"])]
    public function create(Request $request, AnimeRepository $animeRepository): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $animeExists = $animeRepository->findOneBy(['anime_id' => $data['anime_id']]);
        if ($animeExists) {
            return $this->json([
                'message' => 'anime already exists',
            ], 409);
        }

        $anime = new Anime();
        $anime->setName($data['name']);
        $anime->setAnimeId($data['anime_id']);
        $anime->setImage($data['image']);


        $animeRepository->save($anime, true);


        return $this->json([
            'message' => 'anime created success!',
            'data' =>  $anime,
        ], 201, []);
    }

    #[Route('/anime/{id}', name: 'anime_update', methods: ["PUT", "PATCH"])]
    public function update($id, Request $request, AnimeRepository $animeRepository, ManagerRegistry $doctrine): JsonResponse
    {
        if ($request->headers->get("content-type") == "application/json") {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $animeExists = $animeRepository->find($id);

        if (!$animeExists) {
            return $this->json([
                'message' => 'anime not found',
            ], 409);
        }

        if (array_key_exists('name', $data)) $animeExists->setName($data['name']);
        if (array_key_exists('anime_id', $data)) $animeExists->setAnimeId($data['anime_id']);
        if (array_key_exists('image', $data)) $animeExists->setImage($data['image']);
        $doctrine->getManager()->flush();

        return $this->json([
            'message' => 'anime update success!',
            'data' =>  $animeExists,
        ], 200, []);
    }

    #[Route('/anime/{id}', name: 'anime_delete', methods: ["DELETE"])]
    public function delete($id, AnimeRepository $animeRepository): JsonResponse
    {
        $animeExists = $animeRepository->find($id);

        if (!$animeExists) {
            return $this->json([
                'message' => 'anime not found',
            ], 409);
        }

        $animeRepository->remove($animeExists, true);

        return $this->json([], 204, []);
    }
}
