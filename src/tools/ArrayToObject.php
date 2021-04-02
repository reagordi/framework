<?php
/**
 * MediaLife Framework
 *
 * @package reagordi/framework
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Tools;

use stdClass;

class ArrayToObject
{
    private $data;

    public function __construct( $array )
    {
        $object = new stdClass();
        $this->data = $this->array2object( $array, $object );
    }

    public function get()
    {
        return $this->data;
    }

    private function array2object( $array, &$obj )
    {
        foreach ( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                $obj->$key = new stdClass();
                $this->array2object( $value, $obj->$key );
            } else {
                $obj->$key = $value;
            }
        }
        return $obj;
    }
}
