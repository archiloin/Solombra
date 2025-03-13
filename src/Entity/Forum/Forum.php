<?php

namespace App\Entity\Forum;

use App\Repository\Forum\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
#[ORM\Table(name: "forum")]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Topics>
     */
    #[ORM\OneToMany(mappedBy: 'forum', targetEntity: Topic::class)]
    private Collection $topics;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Topics>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopics(Topics $topics): static
    {
        if (!$this->topics->contains($topics)) {
            $this->topics->add($topics);
            $topics->setForum($this);
        }

        return $this;
    }

    public function removeTopics(Topics $topics): static
    {
        if ($this->topics->removeElement($topics)) {
            // set the owning side to null (unless already changed)
            if ($topics->getForum() === $this) {
                $topics->setForum(null);
            }
        }

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        // unset the owning side of the relation if necessary
        if ($post === null && $this->post !== null) {
            $this->post->setAuthor(null);
        }

        // set the owning side of the relation if necessary
        if ($post !== null && $post->getAuthor() !== $this) {
            $post->setAuthor($this);
        }

        $this->post = $post;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        // unset the owning side of the relation if necessary
        if ($message === null && $this->message !== null) {
            $this->message->setAuthor(null);
        }

        // set the owning side of the relation if necessary
        if ($message !== null && $message->getAuthor() !== $this) {
            $message->setAuthor($this);
        }

        $this->message = $message;

        return $this;
    }
}
