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

class Options extends SimpleModel
{
    private static $options = [];

    public function getOptions()
    {
        if ( !self::$options ) {
            $cached_string = Cache::getInstance()->getItem( 'options' );
            if ( is_null( $cached_string->get() ) ) {
                $options = R::find( DB_GLOBAL_PREF . 'options' );
                if ( $options ) {
                    $number_of_seconds = 60; // The cached entry doesn't exist
                    $cached_string->set( $options )->expiresAfter( $number_of_seconds );
                    Cache::getInstance()->save( $cached_string );
                }
            } else {
                $options = $cached_string->get();
            }
            self::$options = [];
            foreach ( $options as $option ) {
                self::$options[$option->name] = $option->value;
            }
        }
        return self::$options;
    }
}
