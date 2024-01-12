<?php

namespace App\Entity;

use App\Repository\OrderPurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderPurchaseRepository::class)]
class OrderPurchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'purchases')]
    private ?self $orderPurchase = null;

    #[ORM\OneToMany(mappedBy: 'orderPurchase', targetEntity: self::class)]
    private Collection $purchases;

    #[ORM\ManyToOne(inversedBy: 'orderPurchases')]
    private ?User $user = null;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderPurchase(): ?self
    {
        return $this->orderPurchase;
    }

    public function setOrderPurchase(?self $orderPurchase): static
    {
        $this->orderPurchase = $orderPurchase;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(self $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setOrderPurchase($this);
        }

        return $this;
    }

    public function removePurchase(self $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getOrderPurchase() === $this) {
                $purchase->setOrderPurchase(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
