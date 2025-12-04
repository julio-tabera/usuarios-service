<?php
/**
 * Created by PhpStorm.
 * User: lazaro
 * Date: 18/04/19
 * Time: 05:10 AM
 */

namespace App\Enum;

class TipoCuentaEnum {

    private static $types = [
        'Cuenta Cliente' => self::cuenta_cliente,

    ];

    const cuenta_cliente = 1;

    public static function getValues(){
        return self::$types;
    }

    public static function fromString($index){
        return self::$types[$index];
    }

    public static function toString($value){
        return array_search($value, self::$types);
    }
}
