<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?OrderPurchase $orderPurchase = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getOrderPurchase(): ?OrderPurchase
    {
        return $this->orderPurchase;
    }

    public function setOrderPurchase(?OrderPurchase $orderPurchase): static
    {
        $this->orderPurchase = $orderPurchase;

        return $this;
    }
}
