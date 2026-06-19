<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profile $author = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 1024)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $positiveVotes = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $negativeVotes = 0;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?Profile
    {
        return $this->author;
    }

    public function setAuthor(?Profile $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getPositiveVotes(): int
    {
        return $this->positiveVotes;
    }

    public function setPositiveVotes(int $positiveVotes): static
    {
        $this->positiveVotes = max(0, $positiveVotes);
        return $this;
    }

    public function addPositiveVote(): static
    {
        ++$this->positiveVotes;
        return $this;
    }

    public function getNegativeVotes(): int
    {
        return $this->negativeVotes;
    }

    public function setNegativeVotes(int $negativeVotes): static
    {
        $this->negativeVotes = max(0, $negativeVotes);
        return $this;
    }

    public function addNegativeVote(): static
    {
        ++$this->negativeVotes;
        return $this;
    }

    public function getReactionsCount(): int
    {
        return $this->positiveVotes + $this->negativeVotes;
    }
}
