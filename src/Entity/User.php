<?php

namespace App\Entity;

use App\Entity\Forum\ForumUser;
use App\Entity\Forum\Message;
use App\Entity\Forum\Post;
use App\Entity\Games\PuzzleMystique\PuzzleProgress;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il y a déjà un compte avec cet adresse mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Empire::class)]
    private Collection $empires;
    
    #[ORM\Column]
    private ?int $solumns = 0;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResearchLevel::class)]
    private Collection $researchLevels;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PuzzleProgress::class, cascade: ['persist', 'remove'])]
    private $puzzleProgresses;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Profil $profil = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class)]
    private Collection $posts;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->empires = new ArrayCollection();
        $this->solumns = 33;
        $this->researchLevels = new ArrayCollection();
        $this->puzzleProgresses = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Empire>
     */
    public function getEmpires(): Collection
    {
        return $this->empires;
    }

    public function addEmpire(Empire $empire): static
    {
        if (!$this->empires->contains($empire)) {
            $this->empires->add($empire);
            $empire->setUser($this);
        }

        return $this;
    }

    public function removeEmpire(Empire $empire): static
    {
        if ($this->empires->removeElement($empire)) {
            // set the owning side to null (unless already changed)
            if ($empire->getUser() === $this) {
                $empire->setUser(null);
            }
        }

        return $this;
    }

    public function getSolumns(): ?int
    {
        return $this->solumns;
    }

    public function setSolumns(int $solumns): static
    {
        $this->solumns = $solumns;

        return $this;
    }
    
    public function getSelectedEmpire(): ?Empire
    {
        foreach ($this->empires as $empire) {
            if ($empire->isSelected()) {
                return $empire;
            }
        }
    
        return null; // Aucun empire sélectionné
    }

    /**
     * @return Collection<int, ResearchLevel>
     */
    public function getResearchLevels(): Collection
    {
        return $this->researchLevels;
    }

    public function addResearchLevel(ResearchLevel $researchLevel): static
    {
        if (!$this->researchLevels->contains($researchLevel)) {
            $this->researchLevels->add($researchLevel);
            $researchLevel->setUser($this);
        }

        return $this;
    }

    public function removeResearchLevel(ResearchLevel $researchLevel): static
    {
        if ($this->researchLevels->removeElement($researchLevel)) {
            // set the owning side to null (unless already changed)
            if ($researchLevel->getUser() === $this) {
                $researchLevel->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PuzzleProgress>
     */
    public function getPuzzleProgresses(): Collection
    {
        return $this->puzzleProgresses;
    }

    public function addPuzzleProgress(PuzzleProgress $puzzleProgress): self
    {
        if (!$this->puzzleProgresses->contains($puzzleProgress)) {
            $this->puzzleProgresses[] = $puzzleProgress;
            $puzzleProgress->setUser($this);
        }

        return $this;
    }

    public function removePuzzleProgress(PuzzleProgress $puzzleProgress): self
    {
        if ($this->puzzleProgresses->removeElement($puzzleProgress)) {
            // set the owning side to null (unless already changed)
            if ($puzzleProgress->getUser() === $this) {
                $puzzleProgress->setUser(null);
            }
        }

        return $this;
    }

    public function getProfil(): ?Profil
    {
        return $this->profil;
    }

    public function setProfil(?Profil $profil): static
    {
        $this->profil = $profil;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }
}
