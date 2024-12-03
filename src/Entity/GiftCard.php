<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GiftCardRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GiftCardRepository::class)]
class GiftCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['gift_card:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['gift_card:read'])]
    private ?float $amount = null;

    #[ORM\Column]
    #[Groups(['gift_card:read'])]
    private ?bool $isRedeemed = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gift_card:read'])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'giftCards')]
    private ?User $owner = null;

    public function __construct()
    {
        $this->isRedeemed = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function isRedeemed(): ?bool
    {
        return $this->isRedeemed;
    }

    public function setRedeemed(bool $isRedeemed): static
    {
        $this->isRedeemed = $isRedeemed;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
