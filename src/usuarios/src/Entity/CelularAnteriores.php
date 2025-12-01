<?php

namespace App\Entity;

use App\Repository\CelularAnterioresRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CelularAnterioresRepository::class)]
#[ORM\Table(name: '`celularanteriores`')]
class CelularAnteriores
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(length: 255)]
    private ?string $celular = null;

    #[ORM\ManyToOne(inversedBy: 'celularesAnteriores')]
    private ?Cliente $cliente = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getCelular(): ?string
    {
        return $this->celular;
    }

    public function setCelular(?string $celular): void
    {
        $this->celular = $celular;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): void
    {
        $this->cliente = $cliente;
    }


}
