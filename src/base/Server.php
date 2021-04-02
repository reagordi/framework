<?php
/**
 * MediaLife Framework
 *
 * @package medialife
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Base;

class Server
{
    /**
     * Информация о сервере
     *
     * @var array
     */
    private static $server;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        self::$server = $_SERVER;
    }

    /**
     * Возвращает порт сервера
     *
     * @return int|null
     */
    public function getServerPort()
    {
        return isset( self::$server['SERVER_PORT'] ) ? self::$server['SERVER_PORT']: null;
    }

    /**
     * Возвращает имя сервера
     *
     * @return string|null
     */
    public function getServerName()
    {
        return isset( self::$server['SERVER_NAME'] ) ? self::$server['SERVER_NAME']: null;
    }

    /**
     * Возвращает адрес сервера
     *
     * @return string|null
     */
    public function getServerAddr()
    {
        return isset( self::$server['SERVER_ADDR'] ) ? self::$server['SERVER_ADDR']: null;
    }

    /**
     * Возвращает SCRIPT_NAME
     *
     * @return string|null
     */
    public function getScriptName()
    {
        return isset( self::$server['SCRIPT_NAME'] ) ? self::$server['SCRIPT_NAME']: null;
    }

    /**
     * Возвращает запрошенный uri
     *
     * @return string|null
     */
    public function getRequestUri()
    {
        return isset( self::$server['REQUEST_URI'] ) ? self::$server['REQUEST_URI']: null;
    }

    /**
     * Возвращает запрошенный uri
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return isset( self::$server['REQUEST_METHOD'] ) ? self::$server['REQUEST_METHOD']: null;
    }

    /**
     * Возвращает PHP_SELF
     *
     * @return string|null
     */
    public function getPhpSelf()
    {
        return isset( self::$server['PHP_SELF'] ) ? self::$server['PHP_SELF']: null;
    }

    /**
     * Возвращает папку ядра системы
     *
     * @return string|null
     */
    public function getPersonalRoot()
    {
        return defined( 'VENDOR_DIR' ) ? VENDOR_DIR: null;
    }

    /**
     * Возвращает DOCUMENT_ROOT сервера
     *
     * @return string|null
     */
    public function getDocumentRoot()
    {
        return isset( self::$server['DOCUMENT_ROOT'] ) ? self::$server['DOCUMENT_ROOT']: null;
    }

    /**
     * Возвращает DOCUMENT_ROOT сервера
     *
     * @return string|null
     */
    public function getHttpHost()
    {
        return isset( self::$server['HTTP_HOST'] ) ? self::$server['HTTP_HOST']: null;
    }
}
