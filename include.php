<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

use Phpfastcache\CacheManager;

if ( Reagordi::$app->options->get( 'components', 'cache', 'type' ) == 'redis' ) {
    CacheManager::setDefaultConfig(new Phpfastcache\Config\ConfigurationOption([
        'host' => Reagordi::$app->options->get( 'components', 'cache', 'value', 'host' ),
        'port' => Reagordi::$app->options->get( 'components', 'cache', 'value', 'port' )
    ]));
} elseif ( Reagordi::$app->options->get( 'components', 'cache', 'type' ) == 'files' ) {
    \Reagordi\Framework\IO\Directory::createDirectory( DATA_DIR . '/cache' );
    CacheManager::setDefaultConfig(new Phpfastcache\Config\ConfigurationOption([
        'path' => DATA_DIR . '/cache'
    ]));
}
Reagordi::$app->context->cache = CacheManager::getInstance( Reagordi::$app->options->get( 'components', 'cache', 'type' ) );

$url = '';
if ( Reagordi::$app->context->request->isHttps() ) $url .= 'https://';
else $url .= 'http://';
$path = str_replace( '\\', '', dirname( Reagordi::$app->context->server->getPhpSelf() ) );
$url .= Reagordi::$app->context->server->getHttpHost() . $path;
define( 'HOME_URL', $url );
unset( $url );

// region Get all routes
$collector = new \Phroute\Phroute\RouteCollector();

if ( is_file( VENDOR_DIR . '/reagordi/cms/include.php' ) ) {
    $_pref = str_replace( '\\', '', dirname( Reagordi::$app->context->server->getPhpSelf() ) );
    if ( $_pref ) {
        $collector->group( array( 'prefix' => '/' . $_pref ), function( \Phroute\Phroute\RouteCollector $collector ) {
            require_once VENDOR_DIR . '/reagordi/cms/include.php';
        });
    } else {
        require_once VENDOR_DIR . '/reagordi/cms/include.php';
    }
} else {
    define( 'DB_PREF', DB_GLOBAL_PREF );

    $path = str_replace( ROOT_DIR . '/', '', APP_DIR );
    define( 'TEMPLATE_URL', str_replace(ROOT_DIR, '', APP_DIR) . '/templates/' );

    $it = new RecursiveDirectoryIterator(  APP_DIR . '/pages/' );

    foreach (new RecursiveIteratorIterator($it) as $endpoint) {
        if ($endpoint->getExtension() == 'php') {
            $_pref = str_replace( '\\', '', dirname( Reagordi::$app->context->server->getPhpSelf() ) );
            if ( $_pref ) {
                $collector->group( array( 'prefix' => '/' . $_pref ), function( \Phroute\Phroute\RouteCollector $collector ){
                    include_once $endpoint;
                });
            } else {
                include_once $endpoint;
            }
        }
    }
}

defined( 'RESPONSE_API' ) or define( 'RESPONSE_API', false );
defined( 'RESPONSE_ADMIN' ) or define( 'RESPONSE_ADMIN', false );

$dispatcher =  new \Phroute\Phroute\Dispatcher($collector->getData());

if ( RESPONSE_API ) {
    require_once __DIR__ . '/tools_api.php';
}

defined('TEMPLATE_URL') or define('TEMPLATE_URL', APP_DIR . '/templates/.default');

try {
    $response = $dispatcher->dispatch(
        Reagordi::$app->context->server->getRequestMethod(),
        parse_url(Reagordi::$app->context->server->getRequestUri(), PHP_URL_PATH)
    );
    if ( RESPONSE_API && is_array( $response ) ) {
        ob_end_clean();
        // region CORS
        header( 'Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS' );
        header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Access-Control-Max-Age: 1728000' );
        header( 'Access-Control-Allow-Origin: *' );
        if ( Reagordi::$app->context->server->getRequestMethod() === 'OPTIONS' ) {
            header( 'Content-Length: 0' );
            die();
        }
        // endregion
        api_send_response( $response );
        exit;
    } else {
        $response = str_replace( '<!--[Reagordi Style]-->', \Reagordi\Framework\Web\Asset::getInstance()->getCss(), $response );
        $response = str_replace( '<!--[Reagordi Js]-->', \Reagordi\Framework\Web\Asset::getInstance()->getJs(), $response );
        if ( Reagordi::$app->options->get( 'components', 'optimize', 'html' ) ) {
            $response = \Reagordi\Framework\Web\Optimize::html( $response );
        }
        echo $response;
    }
} catch (\Phroute\Phroute\Exception\HttpMethodNotAllowedException $exception) {
    if ( RESPONSE_API ) {
        ob_end_clean();
        api_send_response(api_error(
            array(
                'code' => 405,
                'msg' => 'Method not allowed'
            ),
            405,
            'Method not allowed'
        ));
    } else {
        header( 'HTTP/1.1 405 Method not allowed' );
        unset( $collector, $it, $endpoint, $exception, $dispatcher );
        if ( is_file( ROOT_DIR . TEMPLATE_URL . '/405.php' ) ) {
            require_once ROOT_DIR . TEMPLATE_URL . '/405.php';
        } else {
            echo '405 Method not allowed';
        }
        $response = ob_get_clean();
        $response = str_replace( '<!--[Reagordi Style]-->', \Reagordi\Framework\Web\Asset::getInstance()->getCss(), $response );
        $response = str_replace( '<!--[Reagordi Js]-->', \Reagordi\Framework\Web\Asset::getInstance()->getJs(), $response );
        if ( Reagordi::$app->options->get( 'components', 'optimize', 'html' ) ) {
            $response = \Reagordi\Framework\Web\Optimize::html( $response );
        }
        echo $response;
    }
} catch (\Phroute\Phroute\Exception\HttpRouteNotFoundException $exception) {
    if ( RESPONSE_API ) {
        ob_end_clean();
        api_send_response(api_error(
            array(
                'code' => 404,
                'msg' => 'Invalid endpoint'
            ),
            404,
            'Invalid endpoint'
        ));
    } else {
        header( 'HTTP/1.1 404 Not Found' );
        unset( $collector, $it, $endpoint, $exception, $dispatcher );
        if ( is_file( ROOT_DIR . TEMPLATE_URL . '/404.php' ) ) {
            require_once ROOT_DIR . TEMPLATE_URL . '/404.php';
        } else {
            echo '404 Not Found';
        }
        $response = ob_get_clean();
        $response = str_replace( '<!--[Reagordi Style]-->', \Reagordi\Framework\Web\Asset::getInstance()->getCss(), $response );
        $response = str_replace( '<!--[Reagordi Js]-->', \Reagordi\Framework\Web\Asset::getInstance()->getJs(), $response );
        if ( Reagordi::$app->options->get( 'components', 'optimize', 'html' ) ) {
            $response = \Reagordi\Framework\Web\Optimize::html( $response );
        }
        echo $response;
    }
} catch (Phroute\Exception\BadRouteException $exception) {
    if ( RESPONSE_API ) {
        ob_end_clean();
        api_send_response(api_error(
            array(
                'code' => 500,
                'msg' => 'Bad route'
            ),
            500,
            'Bad route'
        ));
    } else {
        header( 'HTTP/1.1 500 Bad route' );
        unset( $collector, $it, $endpoint, $exception, $dispatcher );
        if ( is_file( ROOT_DIR . TEMPLATE_URL . '/500.php' ) ) {
            require_once ROOT_DIR . TEMPLATE_URL . '/500.php';
        } else {
            echo '500 Bad route';
        }
        $response = ob_get_clean();
        $response = str_replace( '<!--[Reagordi Style]-->', \Reagordi\Framework\Web\Asset::getInstance()->getCss(), $response );
        $response = str_replace( '<!--[Reagordi Js]-->', \Reagordi\Framework\Web\Asset::getInstance()->getJs(), $response );
        if ( Reagordi::$app->options->get( 'components', 'optimize', 'html' ) ) {
            $response = \Reagordi\Framework\Web\Optimize::html( $response );
        }
        echo $response;
    }
}
