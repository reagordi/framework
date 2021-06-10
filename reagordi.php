<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

require_once __DIR__ . '/defined.php';

umask(~(REAGORDI_FILE_PERMISSIONS | REAGORDI_DIR_PERMISSIONS) & 0777);

ob_start();
ob_implicit_flush(false);

// Установка внутренней кодировки в UTF-8
!function_exists('mb_internal_encoding') or mb_internal_encoding('UTF-8');

if (REAGORDI_ENV == 'dev' || REAGORDI_ENV == 'test') {
    error_reporting(E_ALL);
    ini_set('html_errors', true);
    ini_set('display_errors', true);
    ini_set('display_startup_errors', true);
}

if (REAGORDI_ENV == 'dev') {
    $whoops = new \Whoops\Run();
    if (REAGORDI_DEV_VIEW == 'html') $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    if (REAGORDI_DEV_VIEW == 'json') $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    if (REAGORDI_DEV_VIEW == 'xml') $whoops->pushHandler(new \Whoops\Handler\XmlResponseHandler());
    if (REAGORDI_DEV_VIEW == 'text') $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
    $whoops->register();
    unset($whoops);
}

require_once __DIR__ . '/check.php';
require_once __DIR__ . '/version.php';
require_once __DIR__ . '/src/loader.php';

\Reagordi\Framework\Loader::registerAutoLoadClasses(
    'reagordi:framework',
    array(
        'Reagordi\\Framework\\Base\\Applicaiton' => __DIR__ . '/src/base/Applicaiton.php',
        'Reagordi\\Framework\\Base\\Context' => __DIR__ . '/src/base/Context.php',
        'Reagordi\\Framework\\Base\\Server' => __DIR__ . '/src/base/Server.php',
        'Reagordi\\Framework\\Base\\Request' => __DIR__ . '/src/base/Request.php',
        'Reagordi\\Framework\\Base\\Mailer' => __DIR__ . '/src/base/Mailer.php',
        'Reagordi\\Framework\\Web\\Cache' => __DIR__ . '/src/web/Cache.php',
        'Reagordi\\Framework\\Web\\View' => __DIR__ . '/src/web/View.php',
        'Reagordi\\Framework\\Web\\Asset' => __DIR__ . '/src/web/Asset.php',
        'Reagordi\\Framework\\Web\\Languages' => __DIR__ . '/src/web/Languages.php',
        'Reagordi\\Framework\\Web\\Optimize' => __DIR__ . '/src/web/Optimize.php',
        'Reagordi\\Framework\\Web\\Components' => __DIR__ . '/src/web/Components.php',
        'Reagordi\\Framework\\Web\\IdentityInterface' => __DIR__ . '/src/web/IdentityInterface.php',
        'Reagordi\\Framework\\Web\\User' => __DIR__ . '/src/web/User.php',
        'Reagordi\\Framework\\Web\\CookieCollection' => __DIR__ . '/src/web/CookieCollection.php',
        'Reagordi\\Framework\\Base\\Session' => __DIR__ . '/src/base/Session.php',
        'Reagordi\\Framework\\Base\\Security' => __DIR__ . '/src/base/Security.php',
        'Reagordi\\Framework\\Base\\SystemException' => __DIR__ . '/src/base/SystemException.php',
        'Reagordi\\Framework\\IO\\File' => __DIR__ . '/src/io/File.php',
        'Reagordi\\Framework\\IO\\Directory' => __DIR__ . '/src/io/Directory.php',
        'Reagordi\\Framework\\Tools\\ArrayToObject' => __DIR__ . '/src/tools/ArrayToObject.php',
        'Reagordi\\Framework\\Tools\\UUIDWriterMySQL' => __DIR__ . '/src/tools/UUIDWriterMySQL.php',
        'Reagordi\\Framework\\Base\\Options' => __DIR__ . '/src/base/Options.php',
    )
);

require_once __DIR__ . '/src/Reagordi.php';

if (REAGORDI_DEBUG_LOG === true) {
    ini_set('log_errors', true);
    \Reagordi\Framework\IO\Directory::createDirectory(DATA_DIR . '/logs/');
    error_log(DATA_DIR . '/logs/php_error.log');
}

require_once __DIR__ . '/tools.php';


$domain_cookie = explode(".", clean_url($_SERVER['HTTP_HOST']));
$domain_cookie_count = count($domain_cookie);
$domain_allow_count = -2;
if ($domain_cookie_count > 2) {
    if (in_array($domain_cookie[$domain_cookie_count - 2], array('com', 'net', 'org'))) $domain_allow_count = -3;
    if ($domain_cookie[$domain_cookie_count - 1] == 'ua') $domain_allow_count = -3;
    $domain_cookie = array_slice($domain_cookie, $domain_allow_count);
}
$domain_cookie = "." . implode(".", $domain_cookie);

/**
 * Префикс Cookie
 *
 * @var string
 */
defined('RG_COOKIE_PREF') or define('RG_COOKIE_PREF', '_rg_' . md5($domain_cookie));

/**
 * Название Cookie языка
 *
 * @var string
 */
defined('RG_COOKIE_LANG') or define('RG_COOKIE_LANG', 'rglang' . RG_COOKIE_PREF);

/**
 * Название Cookie сессии
 *
 * @var string
 */
defined('RG_COOKIE_SID') or define('RG_COOKIE_SID', 'rgsid' . RG_COOKIE_PREF);

Reagordi::getInstance();

if (Reagordi::$app->options->get('components', 'request', 'multiCookieDomain')) {
    if (ip2long($_SERVER['HTTP_HOST']) == -1 or ip2long($_SERVER['HTTP_HOST']) === false) define('DOMAIN', $domain_cookie);
    else define('DOMAIN', null);
} else define('DOMAIN', null);

if (REAGORDI_ENV == 'prod') {
    Reagordi::$app->context->i18n->loadLanguageFile(APP_DIR . '/pages/reagordi.php');
    set_exception_handler(function ($exception) {
        echo '<!DOCTYPE html><html>';
        echo '<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>Reagordi Error</title>';
        echo '<style>a{text-decoration:none}a:hover{text-decoration:underline}</style></head><body>';
        echo Reagordi::$app->context->i18n->getMessage('An error occurred while executing the script. You can enable advanced error output in the settings file');
        echo ' <a href="https://dev.reagordi.com/const" target="_blank">config.php</a>';
        echo '</body></html>';
        return;
    });
}

Reagordi::$app->applicaiton->dbInitPref();

Reagordi::$app->context->i18n->getCurrentLang();

foreach (Reagordi::$app->options->get('modules') as $model) {
    if (is_file(APP_DIR . '/modules/' . $model . '/include.php')) {
        require_once APP_DIR . '/modules/' . $model . '/include.php';
    }
}

if (is_file(APP_DIR . '/php_interface/init.php'))
    require_once APP_DIR . '/php_interface/init.php';
