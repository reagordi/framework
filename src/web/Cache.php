<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Web;

use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\CacheManager;

class Cache
{
    private static $class = null;

    public function __call( $name, $arguments )
    {
        return self::$class;
    }

    public static function __callStatic( $name, $arguments )
    {
        return null;
    }

    public function get()
    {
        return null;
    }

    public static function getInstance()
    {
        if ( self::$class === null ) {
            $type_cache = 'files';
             if ( TYPE_CACHE == 'redis' ) {
                $type_cache = 'redis';
                CacheManager::setDefaultConfig(new ConfigurationOption([
                    'host' => REDIS_HOST,
                    'port' => REDIS_POST
                ]));
                return;
            } else {
                CacheManager::setDefaultConfig(new ConfigurationOption([
                    'path' => DATA_DIR . '/cache/'
                ]));
            }
            if ( TYPE_CACHE == 'none' ) {
                self::$class = new Cache();
                return self::$class;
            }
            self::$class = CacheManager::getInstance( $type_cache );
        }
        return self::$class;
    }
}
