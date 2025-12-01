<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;


// Clase que sea crea con el payload del token que nos
// devuelve JWT del microservicio autenticacion
class User extends JWTUser
{
    private $id;

    public function __construct(string $username, array $roles = [],  $id = null)
    {
        parent::__construct($username, $roles);
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        return new self(
            $username,
            $payload['roles'] ?? [],
            $payload['id'] ?? null // Aquí capturamos el ID del payload
        );
    }

    public function getId()
    {
        return $this->id;
    }
}