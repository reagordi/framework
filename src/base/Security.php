<?php

namespace Reagordi\Framework\Base;


class Security
{
    protected static $obj = null;

    public function generatePasswordHash( $password )
    {
        return password_hash( $password, PASSWORD_DEFAULT );
    }

    public function validatePassword( $password, $hash )
    {
        return password_verify( $password, $hash );
    }

    public static function getInstance()
    {
        if ( self::$obj === null ) {
            self::$obj = new Security();
        }
        return self::$obj;
    }
}
