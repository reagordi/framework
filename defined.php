<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

/**
 * Путь до приложений
 *
 * @var string
 */
defined('APP_DIR') or define('APP_DIR', ROOT_DIR . '/app');

/**
 * Путь до временных данных
 *
 * @var string
 */
defined('DATA_DIR') or define('DATA_DIR', ROOT_DIR . '/data');

/**
 * Тип продукта
 *
 * @var string
 */
defined('REAGORDI_ENV') or define('REAGORDI_ENV', 'prod');

/**
 * Логирование ошибок
 *
 * @var string
 */
defined('REAGORDI_DEBUG_LOG') or define('REAGORDI_DEBUG_LOG', false);

/**
 * Показ выполненных запросов
 *
 * @var string
 */
defined('R_DEBUG') or define('R_DEBUG', false);

/**
 * Права для директорий
 *
 * @var string
 */
defined('REAGORDI_DIR_PERMISSIONS') or define('REAGORDI_DIR_PERMISSIONS', 0755);

/**
 * Права для файлов
 *
 * @var string
 */
defined('REAGORDI_FILE_PERMISSIONS') or define('REAGORDI_FILE_PERMISSIONS', 0644);
