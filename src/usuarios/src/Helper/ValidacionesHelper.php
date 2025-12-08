<?php

namespace App\Helper;

use App\Entity\Cliente;
use App\Validator\EmailRobusto;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidacionesHelper
{
    public function __construct(private ParameterBagInterface $parameterBag)
    {
    }


    // <editor-fold defaultstate="collapsed" desc="Validar Credenciales del Header">
    public function validarCredencialesHeaders($header): bool
    {
        $credencialApi = $this->parameterBag->get('token');
        //dump($credencialApi);die();
        // si el header viene vacio
        if (!$header) {
            return false;
        }
        $client_credentials = base64_decode($header, true);

        // si la decodificación falló
        if ($client_credentials === false) {
            return false;
        }
        // verifica que las credenciales contengan igual longitud
        if (strlen($client_credentials) !== strlen($credencialApi)) {
            return false;
        }
        // si las credenciales no coinciden
        if (!$client_credentials || !hash_equals($credencialApi, $client_credentials)) {
            return false;
        }
        return true;
    }
    //</editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Guardar en el Registro en el Logs">
    public function escribirLog($xml, $name): void
    {
        $fecha = new \DateTime();
        $file = fopen($this->parameterBag->get('kernel.project_dir') . '/public/uploads/logs' . $fecha->format('Ymd') . $name . '.txt', "a+");
        fwrite($file, $fecha->format('H:i:s') . PHP_EOL);
        fwrite($file, $xml . PHP_EOL);
        fclose($file);
    }
    //</editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Validar Campos del Cuerpo ">
    public function validarCamposdelCuerpo($requiredFields, $data)
    {
        foreach ($requiredFields as $field => $message) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return $message;
            }
        }
        return null;
    }
    //</editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Actualizar Cliente con los Campos del Cuerpo ">

    /**
     * @param array $requiredFields
     * @param Cliente $cliente
     * @param ValidatorInterface $validator
     * @return bool|string
     */
    public function actualizarCliente(array $requiredFields, Cliente $cliente, ValidatorInterface $validator): bool|string
    {
        foreach ($requiredFields as $field => $valor) {

            if ($valor === null || trim($valor) === '') {
                continue; // El campo no se actualiza
            }
            switch ($field) {

                case 'name':
                    $cliente->setName($valor);
                    break;

                case 'lastName':
                    $cliente->setLastName($valor);
                    break;

                case 'email':
                    $errors = $validator->validate($valor, new EmailRobusto());
                    if (count($errors) > 0) {
                        return "Email inválido";
                    }
                    $cliente->setEmail($valor);
                    break;

                case 'cellNumber':
                    $cliente->setCellNumber($valor);
                    break;

                case 'identification':
                    $cliente->setIdentification($valor);
                    break;
            }
        }
        return true;
    }


    //</editor-fold>

}
