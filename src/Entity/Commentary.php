<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentaryRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: CommentaryRepository::class)]
class Commentary
{   # Un trait est une sorte de class php qui vous sert a réutiliser des propriété et des setters et getters.
    # Cela est utile lorsque vous avez plusieurs entités qui partagent des propriétés communes
//////////////////////////////////////////////////////////////////////////////////////////////////////
    # Pour utiliser ces deux classes PHP, il vous faudra 2 dépendances PHP de Gedmo : composer require gedmo/doctrine-extensions
    # Timestamp : nbe de seconde depuis la creation d'unix (01/01/70)

    use TimestampableEntity;//permet d'importer les pptes et createdAt updatedAt ss les ecrire 
    use SoftDeleteableEntity;//permet d'importer les pptes et deletedAt ss les ecrire 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'commentaries')]
    #[ORM\JoinColumn(nullable: false)]
    private $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
