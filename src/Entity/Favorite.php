<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\Table(name: "favorites")]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["favorite_show"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["favorite_show"])]
    private ?bool $favorite = null;

    #[ORM\ManyToOne(inversedBy: 'favorites', cascade: ['remove'])]
    #[Groups(["favorite_show"])]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    #[Groups(["favorite_show"])]
    private ?anime $anime = null;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): static
    {
        $this->favorite = $favorite;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAnime(): ?anime
    {
        return $this->anime;
    }

    public function setAnime(?anime $anime): static
    {
        $this->anime = $anime;

        return $this;
    }
}
