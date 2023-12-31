<?php

namespace App\Entity;

use App\Repository\AnimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimeRepository::class)]
#[ORM\Table(name: "animes")]
class Anime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["anime_show", "comment_show", "favorite_show"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["anime_show", "user_show", "comment_show", "favorite_show"])]
    private ?string $name = null;

    #[ORM\Column(unique: true)]
    #[Groups(["anime_show", "user_show", "comment_show", "favorite_show"])]
    private ?int $anime_id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["anime_show", "favorite_show"])]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'anime', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(["anime_show_comments"])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'anime', targetEntity: Favorite::class)]
    private Collection $favorites;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAnimeId(): ?int
    {
        return $this->anime_id;
    }

    public function setAnimeId(int $anime_id): static
    {
        $this->anime_id = $anime_id;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAnime($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAnime() === $this) {
                $comment->setAnime(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->setAnime($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            // set the owning side to null (unless already changed)
            if ($favorite->getAnime() === $this) {
                $favorite->setAnime(null);
            }
        }

        return $this;
    }
}
