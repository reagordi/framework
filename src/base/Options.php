<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Base;

class Options
{
    protected $options = [];
    protected static $obj = null;

    protected function __construct()
    {
        if ( is_file( APP_DIR . '/php_interface/options.php'  ) )
            $this->options = require_once APP_DIR . '/php_interface/options.php';
        $this->defaultOption();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function get()
    {
        $args = func_get_args();
        $data = $this->options;
        foreach ( $args as $arg ) {
            if ( isset( $data[$arg] ) ) {
                $data = $data[$arg];
            } else {
                $data = null;
                break;
            }
        }
        return $data;
    }

    public function __get( $key )
    {
        if ( isset( $this->options[$key] ) ) return $this->options[$key];
        return null;
    }

    private function defaultOption()
    {
        $this->options['url']['api_path'] = isset( $this->options['url']['api_path'] ) ? $this->options['url']['api_path']: 'api';
        $this->options['url']['admin_path'] = isset( $this->options['url']['admin_path'] ) ? $this->options['url']['admin_path']: 'admin';
    }

    public static function getInstance()
    {
        if ( self::$obj === null ) {
            self::$obj = new Options();
        }
        return self::$obj;
    }
}
