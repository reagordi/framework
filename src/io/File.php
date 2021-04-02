<?php
/**
 * MediaLife Framework
 *
 * @package reagordi/framework
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\IO;

class File
{
    public static function hasFile( $file, $algo = 'md5' )
    {
        if ( !self::isFileExists( $file ) ) return null;
        return hash_file( $algo, $file );
    }

    /**
     * Определяет существует ли папка
     *
     * @param string $file Путь до файла
     * @return bool
     */
    public static function isFileExists( $file )
    {
        return is_file( $file );
    }
}
