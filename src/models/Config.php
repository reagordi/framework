<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Models;

use Reagordi\Framework\Cache;
use RedBeanPHP\SimpleModel;
use RedBeanPHP\R;

class Config extends SimpleModel
{
    private static $config = [];

    public function getConfig()
    {
        if ( !self::$config ) {
            $cached_string = Cache::getInstance()->getItem( 'config' );
            if ( is_null( $cached_string->get() ) ) {
                $config = R::find( DB_PREF . 'config' );
                if ( $config ) {
                    $number_of_seconds = 60; // The cached entry doesn't exist
                    $cached_string->set( $config )->expiresAfter( $number_of_seconds );
                    Cache::getInstance()->save( $cached_string );
                }
            } else {
                $config = $cached_string->get();
            }
            self::$config = [];
            foreach ( $config as $conf ) {
                self::$config[$conf->name] = $conf->value;
            }
        }
        return self::$config;
    }
}
