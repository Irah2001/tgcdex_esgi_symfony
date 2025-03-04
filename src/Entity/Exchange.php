<?php

namespace App\Entity;

use App\Repository\ExchangeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRepository::class)]
class Exchange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $executed_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne]
    private ?User $receiver = null;

    /**
     * @var Collection<int, PokemonCard>
     */
    #[ORM\ManyToMany(targetEntity: PokemonCard::class)]
    #[ORM\JoinTable(name: "gain_cards")]
    private Collection $gain_cards;

    /**
     * @var Collection<int, PokemonCard>
     */
    #[ORM\ManyToMany(targetEntity: PokemonCard::class)]
    #[ORM\JoinTable(name: "given_cards")]
    private Collection $given_cards;

    public function __construct()
    {
        $this->gain_cards = new ArrayCollection();
        $this->given_cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executed_at;
    }

    public function setExecutedAt(?\DateTimeInterface $executed_at): static
    {
        $this->executed_at = $executed_at;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * @return Collection<int, PokemonCard>
     */
    public function getGainCards(): Collection
    {
        return $this->gain_cards;
    }

    public function addGainCard(PokemonCard $gainCard): static
    {
        if (!$this->gain_cards->contains($gainCard)) {
            $this->gain_cards->add($gainCard);
        }

        return $this;
    }

    public function removeGainCard(PokemonCard $gainCard): static
    {
        $this->gain_cards->removeElement($gainCard);

        return $this;
    }

    /**
     * @return Collection<int, PokemonCard>
     * cartes données par le créateur de l'échange
     */
    public function getGivenCards(): Collection
    {
        return $this->given_cards;
    }

    public function addGivenCard(PokemonCard $givenCard): static
    {
        if (!$this->given_cards->contains($givenCard)) {
            $this->given_cards->add($givenCard);
        }

        return $this;
    }

    public function removeGivenCard(PokemonCard $givenCard): static
    {
        $this->given_cards->removeElement($givenCard);

        return $this;
    }
}
