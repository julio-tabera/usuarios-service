<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Entity\Nomencladores\NEstadoCliente;
use App\Enum\TipoCuentaEnum;
use App\Helper\EncryptHelper;
use App\Helper\ValidacionesHelper;
use App\Repository\ClienteRepository;
use App\Repository\Nomencladores\NEstadoClienteRepository;
use App\Repository\Nomencladores\NTipoCuentaRepository;
use App\Validator\EmailRobusto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/client')]
class ApiClientServiceController extends AbstractController
{
    //base64 eENyT0xscHU4RkFJZkdjbTBQUjQ0NlJJMFBmUEFTUHg=


    // <editor-fold defaultstate="collapsed" desc="APIS EXTERNAS">

    // <editor-fold defaultstate="collapsed" desc="API REGISTRAR CLIENTES">
    #[Route('/create-clients', methods: ['POST'])]
    public function createClients(Request               $request,
                                  ClienteRepository     $clienteRepository,
                                  NTipoCuentaRepository $tipoCuentaRepository,
                                  ValidatorInterface    $validator,
                                  ValidacionesHelper    $validacionesHelper): JsonResponse
    {
        if ($request->getMethod() == Request::METHOD_POST) {
            try {

                $header = $request->headers->get('client-tokenid');
                if (!$validacionesHelper->validarCredencialesHeaders($header)) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => 'Error en la conexion'
                    ], Response::HTTP_UNAUTHORIZED);
                }

                $data = json_decode($request->getContent(), true);

                // verifica que la codificacion del json haya sido correcta
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => 'Json inválido'
                    ], Response::HTTP_BAD_REQUEST);
                }

                // usuarioId viene del JWT del Auth-Service
                $usuarioId = $this->getUser()->getId();

                // Ahora esto funcionará porque $usuario es instancia de App\Security\User
                // verificacion del usuario del JWT

                if (is_null($usuarioId)) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => 'No se encontraron respuestas'
                    ], Response::HTTP_UNAUTHORIZED);
                }

                // se verifica si las variables estan definidas y no estan vacias en el array data
                $requiredFields = [
                    'name' => 'Nombre usuario requerido',
                    'lastName' => 'Apellidos requeridos',
                    'email' => 'Email requerido',
                    'cellNumber' => 'Móvil requerido',
                    'identification' => 'Identificación requerida'
                ];
                $mensajeErrorField = $validacionesHelper->validarCamposdelCuerpo($requiredFields, $data);
                if (!is_null($mensajeErrorField)) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => $mensajeErrorField
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Validacion de email
                // TODO: falta validar el movil, y la identificacion

                $errorValidacionEmail = $validator->validate($data['email'], new EmailRobusto());
                if (count($errorValidacionEmail) > 0) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => $errorValidacionEmail[0]->getMessage()
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Validación de cliente repetido
                $clienteName = $clienteRepository->findOneBy(['identification' => $data['identification']]);
                if ($clienteName) {
                    return $this->json([
                        'estado' => 'ERROR',
                        'mensaje' => "El cliente ya existe"],
                        Response::HTTP_BAD_REQUEST);
                }
                $cliente = new Cliente();
                $cliente->setUsuarioIdAutenticacionService($usuarioId);
                $cliente->setName($data['name']);
                $cliente->setLastName($data['lastName']);
                $cliente->setEmail($data['email']);
                $cliente->setCellNumber($data['cellNumber']);
                $cliente->setIdentification($data['identification']);
                $cliente->setTipoCuenta($tipoCuentaRepository->find(TipoCuentaEnum::cuenta_cliente));
                $clienteRepository->save($cliente, true);
                return $this->json([
                    'estado' => 'OK',
                    'mensaje' => "Cliente creado correctamente"], Response::HTTP_CREATED);

            } catch (\Exception $ex) {
                $validacionesHelper->escribirLog('ERROR ' . $ex->getMessage(), 'log_create_cliente');
                return $this->json(['estado' => 'ERROR', 'mensaje' => 'Error interno'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
        return $this->json(['estado' => 'ERROR', 'mensaje' => 'No se encontraron respuestas'], Response::HTTP_NOT_ACCEPTABLE);
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="API OBTENER CLIENTE POR IDENTIFICACION">
    #[Route('/find-clients', methods: ['POST'])]
    public function clienteFind(Request            $request,
                                ClienteRepository  $clienteRepository,
                                ValidacionesHelper $validacionesHelper): JsonResponse
    {
        try {
            // Validar credenciales internas
            $header = $request->headers->get('client-tokenid');
            if (!$validacionesHelper->validarCredencialesHeaders($header)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Error en la conexión'
                ], Response::HTTP_UNAUTHORIZED);
            }
            $data = json_decode($request->getContent(), true);

            // verifica que la codificacion del json haya sido correcta
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Json inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            // se verifica si la clave username o id esta definida en el array data y no sean vacias
            if (!isset($data['identification']) || empty(trim($data['identification']))) {
                return $this->json(['estado' => 'ERROR', 'mensaje' => 'Identificacion requerida'], Response::HTTP_BAD_REQUEST);
            }

            // usuarioId viene del JWT del Auth-Service
            $usuarioId = $this->getUser()->getId();

            // Ahora esto funcionará porque $usuario es instancia de App\Security\User
            // verificacion del usuario del JWT

            if (is_null($usuarioId)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'No se encontraron respuestas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Buscar cliente por identificación y por usuario
            $cliente = $clienteRepository->findOneBy([
                'identification' => $data['identification'],
                'usuarioIdAutenticacionService' =>  $usuarioId
            ]);

            if (!$cliente) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Cliente no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $response = $this->json([
                'estado' => 'OK',
                'cliente' => [
                    'id' => $cliente->getId(),
                    'name' => $cliente->getName(),
                    'lastName' => $cliente->getLastName(),
                    'email' => $cliente->getEmail(),
                    'cellNumber' => $cliente->getCellNumber(),
                    'identification' => $cliente->getIdentification(),
                    'tipoCuenta' => $cliente->getTipoCuenta()->getNombre() ?? null
                ]
            ], Response::HTTP_OK);

            // Para evitar que proxies o navegadores almacenen la información del usuario
            $response->headers->set('Cache-Control', 'no-store');
            return $response;

        } catch (\Exception $ex) {
            $validacionesHelper->escribirLog('ERROR ' . $ex->getMessage(), 'log_find_client');
            return $this->json([
                'estado' => 'ERROR',
                'mensaje' => 'Error interno'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="API ACTUALIZAR CLIENTE POR IDENTIFICACION">
    #[Route('/update-clients', methods: ['PUT'])]
    public function clientUpdate(Request            $request,
                                 ClienteRepository  $clienteRepository,
                                 ValidacionesHelper $validacionesHelper,
                                 ValidatorInterface    $validator
    ): JsonResponse
    {
        try {
            // Validar credenciales internas
            $header = $request->headers->get('client-tokenid');
            if (!$validacionesHelper->validarCredencialesHeaders($header)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Error en la conexión'
                ], Response::HTTP_UNAUTHORIZED);
            }
            // Leer JSON
            $data = json_decode($request->getContent(), true);
            // verifica que la codificacion del json haya sido correcta
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Json inválido'
                ], Response::HTTP_BAD_REQUEST);
            }
            // usuarioId viene del JWT del Auth-Service
            $usuarioId = $this->getUser()->getId();

            // Ahora esto funcionará porque $usuario es instancia de App\Security\User
           // verificacion del usuario del JWT
            if (is_null($usuarioId)) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'No se encontraron respuestas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Validar identificación obligatoria
            if (empty(trim($data['identification'] ?? ''))) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Identificación requerida'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Buscar cliente por identificación y por usuario
            $cliente = $clienteRepository->findOneBy([
                'identification' => $data['identification'],
                'usuarioIdAutenticacionService' =>  $usuarioId
            ]);

            if (!$cliente) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => 'Cliente no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // ===========   PROCESO DE ACTUALIZACION  =========

            $requiredFields = [
                'name'           => $data['name']           ?? null,
                'lastName'       => $data['lastName']       ?? null,
                'email'          => $data['email']          ?? null,
                'cellNumber'     => $data['cellNumber']     ?? null,
                'identification' => $data['identification'] ?? null,
            ];

            // Actualizar cliente usando el helper
            $resultado = $validacionesHelper->actualizarCliente($requiredFields, $cliente, $validator);
            if ($resultado !== true) {
                return $this->json([
                    'estado' => 'ERROR',
                    'mensaje' => $resultado  // mensaje exacto del error
                ], Response::HTTP_BAD_REQUEST);
            }

            $clienteRepository->save($cliente, true);

            return $this->json([
                'estado' => 'OK',
                'mensaje' => 'Cliente actualizado correctamente'
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            $validacionesHelper->escribirLog('ERROR ' . $ex->getMessage(), 'log_update_client');
            return $this->json([
                'estado' => 'ERROR',
                'mensaje' => 'Error interno'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
// </editor-fold>

    // </editor-fold>

}
