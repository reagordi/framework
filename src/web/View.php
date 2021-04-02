<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Web;

use Reagordi\Framework\Base\SystemException;
use Reagordi\Framework\Config\Config;
use Reagordi\Framework\IO\Directory;
use Reagordi\Framework\IO\File;
use \Smarty;
// extends Smarty
class View
{
    public $layout = 'main';
    private $document_params = [];
    private $thema_path = [];
    private static $obj = null;

    public static function getInstance()
    {
        if ( self::$obj === null ) {
            self::$obj = new View();
        }
        return self::$obj;
    }

    protected function __construct()
    {
        /**parent::__construct();

        if ( Config::getInstance()->get( 'cache', 'lifetime' ) && Config::getInstance()->get( 'cache', 'status' ) == '1' ) {
            $this->caching = true;
            $this->cache_lifetime = Config::getInstance()->get( 'cache', 'status' );
            Directory::createDirectory( DATA_DIR . '/view/' );
        }

        Directory::createDirectory( DATA_DIR . '/templates_c/' );

        $this->addDir( VENDOR_DIR . '/reagordi/cms/templates/' );
        //$this->addDir( APP_DIR . '/templates/' . Config::getInstance()->get( 'theme', 'site' ) . '/' );

        $this->compile_dir = DATA_DIR . '/templates_c/';
        $this->cache_dir = DATA_DIR . '/view/';*/
    }

    public function assign( $key, $value )
    {
        if ( !is_string( $key ) ) throw new SystemException( 'The key name must be a string value' );
        $this->document_params[$key] = $value;
    }

    public function setTemplateDir( $path )
    {
        if ( in_array( $path, $this->thema_path ) || !is_dir( $path ) ) return false;
        $this->thema_path[] = $path;
        return true;
    }

    public function render( $file, $params = [] )
    {
        foreach ( $this->thema_path as $thema ) {
            $path = $thema . '/' . $file . '.php';
            if ( File::isFileExists( $file )  ) $path = $file;
            if ( File::isFileExists( $path ) ) {
                ob_start();
                extract( $this->document_params );
                extract( $params );
                require_once $path;
                return ob_get_clean();
            }
        }
    }

    public function display( $file, $params = [] )
    {
        echo $this->render( $file, $params );
    }

    public function fech()
    {
        return $this->render( $this->layout );
    }
}
