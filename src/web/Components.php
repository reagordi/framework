<?php
/**
 * MediaLife Framework
 *
 * @package reagordi/framework
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Web;

use Reagordi\Framework\Base\SystemException;
use Reagordi\Framework\Web\View;
use Reagordi\Framework\IO\File;
use Reagordi;

class Components
{
    /**
     * Объект класса
     *
     * @var null|Languages
     */
    private static $obj = null;

    /**
     * Экземпляр шаблонизатора
     *
     * @var \Reagordi\Framework\View
     */
    public $view;

    /**
     * Массив параметров компонента
     *
     * @var array
     */
    public $ar_params = array();

    /**
     * Путь до шаблона
     *
     * @var string
     */
    private $thema;

    private static $count_component = 1;

    /**
     * Components constructor.
     */
    public function __construct()
    {
        $this->view = View::getInstance();
    }

    /**
     * Подключение компонента
     *
     * @param string $module_name Имя модуля
     * @param string $component_name Имя компонента
     * @param string $component_template Шаблон, с которым вызывается компонент
     * @param array $ar_params Параметры компонента
     * @param bool $parent_template Вести поиск в директории компонента
     * @return mixed
     */
    public function includeComponent(
        $module_name,
        $component_name,
        $component_template = 'defalut',
        $ar_params = array(),
        $parent_template = true
    ) {
        if ( $module_name == 'reagordi:system' ) {
            return;
        }
        $module_name = explode( ':', $module_name );
        if ( count( $module_name ) != 2 ) throw new SystemException( 'You passed an invalid module name' );
        $module_name = implode('/', $module_name);
        $path = APP_DIR . '/modules/' . $module_name . '/components/' . $component_name;

        if ( in_array( $module_name, Reagordi::getInstance()->getConfig()->get( 'modules' ) ) === false ) {
            $message = 'The ' . $component_name . ' component cannot be loaded. The ' . str_replace( '/', ':', $module_name ) . ' module is disabled and / or removed';
            if ( REAGORDI_ENV == 'prod' ) {
                echo '<div class="reagordi_error">' . $message . '</div>';
                return;
            }
            throw new SystemException( $message, 1, E_NOTICE );
            unset($message);
        }

        $path_file = $path . '/templates/' . $component_template;
        if ( $parent_template ) {
            if ( RESPONSE_ADMIN ) {
                $path_file = APP_DIR . '/templates/';
                $path_file .= Reagordi::getInstance()->getConfig()->get( 'theme', 'admin' );
                $path_file .= '/components/' . $module_name . '/' . $component_name . '/';
                $path_file .= $component_template;
            } else {
                $path_file = APP_DIR . '/templates/';
                $path_file .= Reagordi::getInstance()->getConfig()->get( 'theme', 'site' );
                $path_file .= '/components/' . $module_name . '/' . $component_name . '/';
                $path_file .= $component_template;
            }
        }

        if ( File::isFileExists( $path_file . '/' . $component_name . '.css' ) ) {
            Asset::getInstance()->addCss( $path_file . '/' . $component_name . '.css', self::$count_component * 1000 );

        }
        if ( File::isFileExists( $path_file . '/' . $component_name . '.js' ) ) {
            Asset::getInstance()->addJs( $path_file . '/' . $component_name . '.js', self::$count_component * 1000 );
        }
        $path_file .= '/template.php';

        if ( !File::isFileExists( $path_file ) )
            throw new SystemException( 'The subject ' . $component_template . ' of the component ' . str_replace( '/', ':', $module_name ) . ' ' . $component_name . ' was not found' );
        $this->thema = $path_file;

        if ( is_array( $ar_params ) ) $this->ar_params = $ar_params;

        self::$count_component++;

        if ( is_file( $path . '/component.php' ) ) require_once $path . '/component.php';
    }

    /**
     * Подключение темы компонента
     *
     * @throws \SmartyException
     * @return mixed
     */
    public function includeComponentTemplate()
    {
        foreach ( $this->ar_params as $key => $value ) {
            if ( is_string( $key ) ) $this->view->assign( $key, $value );
        }
        return $this->view->display( $this->thema );
    }

    /**
     * Returns current instance of the Languages.
     *
     * @return Components
     */
    public static function getInstance()
    {
        if ( self::$obj === null ) {
            self::$obj = new Components();
        }
        return self::$obj;
    }
}
