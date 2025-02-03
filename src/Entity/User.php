<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $is_vip = null;

    /**
     * @var Collection<int, PokemonCard>
     */
    #[ORM\ManyToMany(targetEntity: PokemonCard::class)]
    private Collection $pokedex;

    public function __construct()
    {
        $this->pokedex = new ArrayCollection();
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isVip(): ?bool
    {
        return $this->is_vip;
    }

    public function setIsVip(bool $is_vip): static
    {
        $this->is_vip = $is_vip;

        return $this;
    }

    /**
     * @return Collection<int, PokemonCard>
     */
    public function getPokedex(): Collection
    {
        return $this->pokedex;
    }

    public function addPokedex(PokemonCard $pokedex): static
    {
        if (!$this->pokedex->contains($pokedex)) {
            $this->pokedex->add($pokedex);
        }

        return $this;
    }

    public function removePokedex(PokemonCard $pokedex): static
    {
        $this->pokedex->removeElement($pokedex);

        return $this;
    }
}
