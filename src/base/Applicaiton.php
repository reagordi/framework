<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Base;

class Applicaiton
{
    /**
     * Экземпляр класса Application
     *
     * @var Application
     */
    protected static $instance = null;

    /**
     * Экземпляр класса  context.
     *
     * @var Context
     */
    protected $context;

    private $config_db = null;
    private $db_init = false;

    /**
     * Applicaiton constructor.
     */
    public function initialize()
    {
        $this->context = new Context();
    }

    /**
     * Returns context of the current request.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Возвращает DOCUMENT_ROOT сервера
     *
     * @return string|null
     */
    public function getDocumentRoot()
    {
        return $this->context->getServer()->getDocumentRoot();
    }

    private function dbConfig()
    {
        $db_config_err = false;

        $db = $this->config_db;

        if ($db === null) {
            if (REAGORDI_ENV == 'dev' && is_file(APP_DIR . '/php_interface/db_dev.php')) {
                $db = require_once APP_DIR . '/php_interface/db_dev.php';
            } elseif (REAGORDI_ENV == 'test' &&
                is_file(APP_DIR . '/php_interface/db_test.php')) {
                $db = require_once APP_DIR . '/php_interface/db_test.php';
            } elseif (is_file(APP_DIR . '/php_interface/db.php'))
                $db = require_once APP_DIR . '/php_interface/db.php';
            else {
                $db_config_err = true;
            }
        }
        if (
            !isset($db) ||
            !is_array($db) ||
            !isset($db['dsn']) ||
            !isset($db['username']) ||
            !isset($db['password']) ||
            !isset($db['prefix'])
        ) {
            $db_config_err = true;
        }

        if ($db_config_err) {
            if (PHP_SAPI !== 'cli') {
                if (is_dir(ROOT_DIR . '/install/')) {
                    $server = Context::getCurrent()->getServer();
                    $path = '';
                    if (Context::getCurrent()->getRequest()->isHttps()) {
                        $path .= 'https://';
                    } else $path .= 'http://';
                    $path .= $server->getHttpHost() .
                        str_replace('\\', '', dirname($server->getPhpSelf()));
                    header('Location: ' . $path . '/install/');
                }
            }
            die('Reagordi Framework is not installed. To install, go to /install/');
        }
        unset($db_config_err);
        $this->config_db = $db;

        return $db;
    }

    public function dbInitPref()
    {
        $db = $this->dbConfig();

        /**
         * Глобальный префикс таблиц
         *
         * @var string
         */
        defined('DB_GLOBAL_PREF') or define('DB_GLOBAL_PREF', $db['prefix']);
    }

    public function dbInit()
    {
        if ($this->db_init) return;

        $db = $this->dbConfig();

        \RedBeanPHP\R::setup($db['dsn'], $db['username'], $db['password']);

        if (!\RedBeanPHP\R::testConnection()) {
            try {
                $pdo = new \PDO($db['dsn'], $db['username'], $db['password']);
            } catch (\PDOException $e) {
                throw new SystemException(
                    'DataBase no conect: ' . $e->getMessage(),
                    $e->getCode(),
                    E_ERROR
                );
            }
        }
        unset($db);

        if (R_DEBUG === true) {
            //turns debugging ON (recommended way)
            \RedBeanPHP\R::fancyDebug(true);

            //turns debugging ON (classic)
            \RedBeanPHP\R::debug(true, 2);
        }

        // Разрешает использовать префикс
        \RedBeanPHP\R::ext(
            'xdispense',
            function ($type) {
                return \RedBeanPHP\R::getRedBean()->dispense($type);
            }
        );
        // R::xdispense( 'cms_page' );

        $old_tool_box = \RedBeanPHP\R::getToolBox();
        $old_adapter = $old_tool_box->getDatabaseAdapter();
        $uuid_writer = new \Reagordi\Framework\Tools\UUIDWriterMySQL( $old_adapter );
        $new_red_bean = new \RedBeanPHP\OODB( $uuid_writer );
        $new_tool_box = new \RedBeanPHP\ToolBox( $new_red_bean, $old_adapter, $uuid_writer );
        \RedBeanPHP\R::configureFacadeWithToolbox( $new_tool_box );

        if (is_file(APP_DIR . '/php_interface/after_db.php')) {
            require_once APP_DIR . '/php_interface/after_db.php';
        }
        $this->db_init = true;
    }

    /**
     * Returns current instance of the Application.
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
            static::$instance->initialize();
        }
        return static::$instance;
    }
}
