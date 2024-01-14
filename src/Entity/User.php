<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nickname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    #[ORM\Column]
    private array $roles=['ROLE_USER'];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: OrderPurchase::class)]
    private Collection $orderPurchases;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rating::class)]
    private Collection $ratings;

    public function __construct()
    {
        $this->orderPurchases = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }


    public function getRoles(): array
    {
        return $this->roles;
    }


    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return Collection<int, OrderPurchase>
     */
    public function getOrderPurchases(): Collection
    {
        return $this->orderPurchases;
    }

    public function addOrderPurchase(OrderPurchase $orderPurchase): static
    {
        if (!$this->orderPurchases->contains($orderPurchase)) {
            $this->orderPurchases->add($orderPurchase);
            $orderPurchase->setUser($this);
        }

        return $this;
    }

    public function removeOrderPurchase(OrderPurchase $orderPurchase): static
    {
        if ($this->orderPurchases->removeElement($orderPurchase)) {
            // set the owning side to null (unless already changed)
            if ($orderPurchase->getUser() === $this) {
                $orderPurchase->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setUser($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }

        return $this;
    }
}
