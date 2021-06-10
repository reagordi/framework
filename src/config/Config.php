<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Config;

use Reagordi\Framework\Base\Context;

class Config
{
    protected $config = [];
    protected static $obj = null;

    protected function __construct()
    {
        if (RG_ALLOW_MULTISITE && is_file(APP_DIR . '/php_interface/config/' . Context::getCurrent()->getServer()->getHttpHost() . '.php'))
            $this->config = require_once APP_DIR . '/php_interface/config/' . Context::getCurrent()->getServer()->getHttpHost() . '.php';
        elseif (!RG_ALLOW_MULTISITE && is_file(APP_DIR . '/php_interface/config.php'))
            $this->config = require_once APP_DIR . '/php_interface/config.php';
        $this->defaultConfig();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function get()
    {
        $args = func_get_args();
        $data = $this->config;
        foreach ($args as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                $data = null;
                break;
            }
        }
        return $data;
    }

    public function __get($key)
    {
        if (isset($this->config[$key])) return $this->config[$key];
        return null;
    }

    protected function defaultConfig()
    {
        $this->config['id'] = isset($this->config['id']) ? $this->config['id'] . '_' : '';
        $this->config['site_online'] = isset($this->config['site_online']) ? $this->config['site_online'] : false;
        $this->config['theme']['site'] = isset($this->config['theme']['site']) ? $this->config['theme']['site'] : '.default';
        $this->config['theme']['admin'] = isset($this->config['theme']['admin']) ? $this->config['theme']['admin'] : '.default';
        $this->config['modules'] = isset($this->config['modules']) ? $this->config['modules'] : [];
        $this->config['router'] = isset($this->config['router']) ? $this->config['router'] : [];
    }

    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Config();
        }
        return self::$obj;
    }
}
