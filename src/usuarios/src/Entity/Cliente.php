<?php

namespace App\Entity;

use App\Entity\CelularAnteriores;
use App\Entity\EmailAnteriores;
use App\Entity\Nomencladores\NEstadoCliente;
use App\Entity\Nomencladores\NTipoCuenta;
use App\Helper\EncryptHelper;
use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
#[UniqueEntity(
    fields: ['usuarioIdAutenticacionService'],
    message: 'Este usuario ya existe. No puede ser creado un segundo cliente con el mismo usuario .',
    errorPath: 'usuarioIdAutenticacionService',
)]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private int $usuarioIdAutenticacionService; // referencia al ID del Usuario (Auth-Service)

    #[ORM\Column(length:255)]
    private string $name;

    #[ORM\Column(length:255)]
    private string $lastName;

    #[ORM\Column(length:255)]
    private string $email;

    #[ORM\Column(length:50)]
    private string $cellnumber;

    #[ORM\Column(length:100)]
    private string $identification;

    #[ORM\Column(nullable: true)]
    private ?bool $validCell;

    #[ORM\Column(nullable: true)]
    private ?int $codigoCell;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

//    #[ORM\ManyToOne]
//    private ?NEstadoCliente $estadoCliente = null;

    #[ORM\ManyToOne(inversedBy: 'cliente')]
    private ?NTipoCuenta $tipoCuenta;


    #[ORM\OneToMany(targetEntity: EmailAnteriores::class, mappedBy: 'cliente', cascade: ['persist', 'remove'])]
    private Collection $emailsAnteriores;

    #[ORM\OneToMany(targetEntity: CelularAnteriores::class, mappedBy: 'cliente', cascade: ['persist', 'remove'])]
    private Collection $celularesAnteriores;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->emailsAnteriores = new ArrayCollection();
        $this->celularesAnteriores = new ArrayCollection();
        $this->validCell = false;
        $encryptHelper = new EncryptHelper();
        $this->codigoCell = $encryptHelper->generateRandomString(6,true,false, false);
    }

    public function __toString(): string
    {
        return $this->name . ' ' . $this->lastName;
    }

    public function getNombreCompleto(): string
    {
        return $this->name . ' ' . $this->lastName;
    }

    public function getUsuarioIdAutenticacionService(): int
    {
        return $this->usuarioIdAutenticacionService;
    }

    public function setUsuarioIdAutenticacionService(int $usuarioIdAutenticacionService): void
    {
        $this->usuarioIdAutenticacionService = $usuarioIdAutenticacionService;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function getCellnumber(): ?string
    {
        return $this->cellnumber;
    }

    public function setCellnumber(string $cellnumber): static
    {
        $this->cellnumber = $cellnumber;

        return $this;
    }

    public function getIdentification(): ?string
    {
        return $this->identification;
    }

    public function setIdentification(string $identification): static
    {
        $this->identification = $identification;

        return $this;
    }


    public function isValidCell(): ?bool
    {
        return $this->validCell;
    }

    public function setValidCell(?bool $validCell): static
    {
        $this->validCell = $validCell;

        return $this;
    }

    public function getCodigoCell(): ?int
    {
        return $this->codigoCell;
    }

    public function setCodigoCell(?int $codigoCell): static
    {
        $this->codigoCell = $codigoCell;

        return $this;
    }

    private ?int $codigoCellForm = null;
    public function getCodigoCellForm(): ?int
    {
        return $this->codigoCellForm;
    }

    public function setCodigoCellForm(?int $codigoCellForm): static
    {
        $this->codigoCellForm = $codigoCellForm;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }




    public function getTipoCuenta(): ?NTipoCuenta
    {
        return $this->tipoCuenta;
    }

    public function setTipoCuenta(?NTipoCuenta $tipoCuenta): void
    {
        $this->tipoCuenta = $tipoCuenta;
    }

//    public function getEstadoCliente(): ?NEstadoCliente
//    {
//        return $this->estadoCliente;
//    }
//
//    public function setEstadoCliente(?NEstadoCliente $estadoCliente): void
//    {
//        $this->estadoCliente = $estadoCliente;
//    }



    //====================== RELACIONES =======================//

    /**
     * @return Collection<int, EmailAnteriores>
     */
    public function getEmailsAnteriores(): Collection
    {
        return $this->emailsAnteriores;
    }

    public function addEmailsAnteriores(EmailAnteriores $emailsAnteriores): static
    {
        if (!$this->emailsAnteriores->contains($emailsAnteriores)) {
            $this->emailsAnteriores->add($emailsAnteriores);
            $emailsAnteriores->setCliente($this);
        }

        return $this;
    }

    public function removeCEmailsAnteriores(EmailAnteriores $emailsAnteriores): static
    {
        if ($this->emailsAnteriores->removeElement($emailsAnteriores)) {
            // set the owning side to null (unless already changed)
            if ($emailsAnteriores->getCliente() === $this) {
                $emailsAnteriores->setCliente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, CelularAnteriores>
     */
    public function getCelularesAnteriores(): Collection
    {
        return $this->celularesAnteriores;
    }

    public function addCelularesAnteriores(CelularAnteriores $celularAnteriores): static
    {
        if (!$this->celularesAnteriores->contains($celularAnteriores)) {
            $this->celularesAnteriores->add($celularAnteriores);
            $celularAnteriores->setCliente($this);
        }

        return $this;
    }

    public function removeCelularAnteriores(CelularAnteriores $celularAnteriores): static
    {
        if ($this->celularesAnteriores->removeElement($celularAnteriores)) {
            // set the owning side to null (unless already changed)
            if ($celularAnteriores->getCliente() === $this) {
                $celularAnteriores->setCliente(null);
            }
        }

        return $this;
    }

}
