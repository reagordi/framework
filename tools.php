<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

/**
 * Перевод
 *
 * @param string $key Ключ перевода
 */
function t( string $key ) {
    return Reagordi::$app->context->i18n->getMessage( $key );
}
