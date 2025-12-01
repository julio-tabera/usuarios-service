<?php

namespace App\Entity\Nomencladores;

use App\Entity\Cliente;
use App\Repository\Nomencladores\NTipoCuentaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NTipoCuentaRepository::class)]
#[ORM\Table(name: '`n_tipocuenta`')]
class NTipoCuenta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(nullable: true)]
    private ?bool $enabled;

    #[ORM\OneToMany(targetEntity: Cliente::class, mappedBy: 'tipoCuenta')]
    private Collection $cliente;

    public function __construct()
    {
        $this->enabled = true;
        $this->cliente = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nombre;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }


    /**
     * @return Collection<int, Cliente>
     */
    public function getCliente(): Collection
    {
        return $this->cliente;
    }

    public function addCliente(Cliente $cliente): static
    {
        if (!$this->cliente->contains($cliente)) {
            $this->cliente->add($cliente);
            $cliente->setTipoCuenta($this);
        }

        return $this;
    }

    public function removeCliente(Cliente $cliente): static
    {
        if ($this->cliente->removeElement($cliente)) {
            // set the owning side to null (unless already changed)
            if ($cliente->getTipoCuenta() === $this) {
                $cliente->setTipoCuenta(null);
            }
        }

        return $this;
    }


}
