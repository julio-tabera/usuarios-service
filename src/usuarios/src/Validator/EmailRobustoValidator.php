<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailRobustoValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var EmailRobusto $constraint */

        if ($value === null || $value === '') {
            return;
        }

        $value = trim($value);

        // Validación de longitud (RFC 5321)
        if (strlen($value) > 254) {
            $this->context->buildViolation($constraint->messageLongitud)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Validación de formato RFC usando FILTER_VALIDATE_EMAIL
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->context->buildViolation($constraint->messageFormato)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Validar dominio (MX obligatorio para recibir correo)
        $domain = substr(strrchr($value, '@'), 1);

        if (!$domain || !checkdnsrr($domain, 'MX')) {
            $this->context->buildViolation($constraint->messageDomino)
                ->setParameter('{{ domain }}', $domain)
                ->addViolation();
        }
    }
}
