<?php

namespace App\Entity\Nomencladores;

use App\Repository\Nomencladores\NEstadoClienteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NEstadoClienteRepository::class)]
#[ORM\Table(name: '`n_estadocliente`')]
class NEstadoCliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    public function __construct()
    {
        $this->enabled = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function __toString(): string
    {
        return $this->nombre;
    }
      public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
