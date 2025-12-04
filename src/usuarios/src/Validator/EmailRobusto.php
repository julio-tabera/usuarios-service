<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Email robusto para validaciones en API/Forms.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EmailRobusto extends Constraint
{
    public string $messageFormato = 'El email "{{ value }}" no tiene un formato válido.';
    public string $messageLongitud = 'El email "{{ value }}" excede la longitud permitida (máx. 254 caracteres).';
    public string $messageDomino = 'El dominio "{{ domain }}" no es válido o no posee registros MX.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
