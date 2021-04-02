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

class Modules extends SimpleModel
{
    private static $modules = [];

    public function getModulesActive()
    {
        if ( !self::$modules ) {
            $cached_string = Cache::getInstance()->getItem( 'modules' );
            if ( is_null( $cached_string->get() ) ) {
                $options = R::find( DB_PREF . 'modules' );
                if ( $options ) {
                    $number_of_seconds = 60; // The cached entry doesn't exist
                    $cached_string->set( $options )->expiresAfter( $number_of_seconds );
                    Cache::getInstance()->save( $cached_string );
                }
            } else {
                $options = $cached_string->get();
            }
            self::$modules = [];
            foreach ( $options as $option ) {
                self::$modules[] = $option->name;
            }
        }
        return self::$modules;
    }
}
