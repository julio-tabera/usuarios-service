<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Entity\Nomencladores\NEstadoCliente;
use App\Helper\EncryptHelper;
use App\Repository\ClienteRepository;
use App\Repository\Nomencladores\NEstadoClienteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
 class ApiClientServiceController extends AbstractController
{
    #[Route('/create-clients', methods: ['POST'])]
    public function createClients( Request $request,
                                   NEstadoClienteRepository $estadoClienteRepository,
                                   ClienteRepository $clienteRepository,
                                   EncryptHelper $encryptHelper): JsonResponse {

        try {
            $data = json_decode($request->getContent(), true);

            // verifica que la codificacion del json haya sido correcta
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Json inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            // usuarioId viene del JWT del Auth-Service
            // Ahora esto funcionará porque $usuario es instancia de App\Security\User
            $usuarioId = $this->getUser()->getId();

            // usuario no encontrado
            if (is_null($usuarioId)) {
                return $this->json([
                    'estado'  => 'ERROR',
                    'mensaje' => 'No se encontraron respuestas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // se verifica si las variables estan definidas y no estan vacias en el array data
            if (!isset($data['name']) && is_null($data['name'])) {
                return $this->json(['estado' => 'error', 'mensaje' => 'Nombre usuario requerido'], Response::HTTP_BAD_REQUEST);
            }elseif (!isset($data['lastName']) && is_null($data['lastName'])){
                return $this->json(['estado' => 'error', 'mensaje' => 'Apellidos de usuario requerido'], Response::HTTP_BAD_REQUEST);
            }elseif (!isset($data['email']) && is_null($data['email'])){
                return $this->json(['estado' => 'error', 'mensaje' => 'Email de usuario requerido'], Response::HTTP_BAD_REQUEST);
            }elseif (!isset($data['cellNumber']) && is_null($data['cellNumber'])){
                return $this->json(['estado' => 'error', 'mensaje' => 'Móvil de usuario requerido'], Response::HTTP_BAD_REQUEST);
            }elseif (!isset($data['identification']) && is_null($data['identification'])){
                return $this->json(['estado' => 'error', 'mensaje' => 'Identificación de usuario requerido'], Response::HTTP_BAD_REQUEST);
            }

            $cliente = new Cliente();
            $cliente->setUsuarioIdAutenticacionService($usuarioId);
            $cliente->setName($data['name']);
            $cliente->setLastName($data['lastName']);
            $cliente->setEmail($data['email']);
            $cliente->setCellNumber($data['cellNumber']);
            $cliente->setIdentification($data['identification']);

            // estado por default
            $estado = $estadoClienteRepository->find(1);
            $cliente->setEstadoCliente($estado);

            $clienteRepository->save($cliente);


            return $this->json([
                'estado' => 'OK',
                'cliente_id' => $cliente->getId()
            ], 201);


        }catch (\Exception $e){
            dump($e->getMessage());
        }

        return $this->json([
            'estado' => 'OK',
            'cliente_id' => $cliente->getId()
        ], 201);


    }

}
