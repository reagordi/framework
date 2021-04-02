<?php

namespace Reagordi\Framework\Web;

use Reagordi\Framework\IO\Directory;
use Reagordi\Framework\Web\View;
use Reagordi\Framework\IO\File;
use Reagordi;

class Asset
{
    /**
     * Объект текущего класса
     *
     * @var Asset
     */
    private static $instance;

    private $count_css = 0;
    private $count_js = 0;
    private $css = [];
    private $js = [];

    /**
     * Возращает истину если указанный путь это сторонний ресурс
     *
     * @param string $src Ссылка
     * @return bool
     */
    private static function IsExternalLink( $src )
    {
        return ( strncmp( $src, 'http://', 7 ) == 0 || strncmp( $src, 'https://', 8 ) == 0 || strncmp( $src, '//', 2 ) == 0 );
    }

    /**
     * Получение ссылки на файл
     *
     * @param string $src Путь до файла
     * @return string
     */
    public function getAssetPath( $src )
    {
        if ( ( $p = mb_strpos( $src, '?' ) ) > 0 && !$this->IsExternalLink( $src ) ) {
            $src = mb_substr( $src, 0, $p );
        }
        return $src;
    }

    /**
     * Путь до стилей
     *
     * @param string $path Путь до файла
     * @return string
     */
    public function addCss( $path, $parent = false )
    {
        if ( $path == '' ) return false;
        if ( is_array( $path ) ) {
            foreach ( $path as $file ) {
                $this->addCss( $file, $parent );
            }
            return '';
        }
        $css = $this->getAssetPath( $path );
        if ( !in_array( $css, $this->css ) ) {
            if ( $parent === false ) {
                $this->css[$this->count_css] = $css;
                $this->count_css++;
            } else {
                if ( isset( $this->css[$parent] ) ) $parent = rand(111111, 999999);
                $this->css[$parent] = $css;
            }
        }
        return '';
    }


    /**
     * Путь до скриптов
     *
     * @param string $path Путь до файла
     * @return bool
     */
    public function addJs( $path, $parent = false  )
    {
        if ( $path == '' ) return false;
        if ( is_array( $path ) ) {
            foreach ( $path as $file ) {
                $this->addJs( $file );
            }
            return '';
        }
        $js = $this->getAssetPath( $path );
        if ( !in_array( $js, $this->js ) ) {
            if ( $parent === false ) {
                $this->js[$this->count_js] = $js;
                $this->count_js++;
            } else {
                if ( isset( $this->js[$parent] ) ) $parent = rand(111111, 999999);
                $this->js[$parent] = $js;
            }
        }
        return '';
    }

    public function getCss()
    {
        $src = '';
        ksort( $this->css );
        foreach ( $this->css as $css ) {
            /*if ( !$this->IsExternalLink( $css ) ) {
                $thema = Reagordi::getInstance()->getConfig()->get( 'theme', 'site' );
                if ( RESPONSE_ADMIN ) $thema = Reagordi::getInstance()->getConfig()->get( 'theme', 'admin' );
                $path = $css;
                if ( File::isFileExists( $path ) ) {
                    $asset = substr( File::hasFile( $path ), 0, 7 );
                    $file = explode( '/', $path );
                    $file = $file[count( $file ) - 1];
                    //$file = substr( File::hasFile( $path ), 7, 7 ) . '.css';
                    if (
                        !File::isFileExists( ROOT_DIR  . '/' . $asset . '/' . $file ) ||
                        REAGORDI_ENV == 'dev'
                    ) {
                        $_context = file_get_contents( $path );
                        $context = $this->fixCssIncludes( $_context, ASSET_DIR . '/' . $asset, dirname( $path ) );
                        if ( Reagordi::getInstance()->getConfig()->get( 'optimize', 'gzip_css' ) ) {
                            $context = Optimize::css( $context );
                        }
                        Directory::createDirectory( ROOT_DIR  . '/' . $asset );
                        file_put_contents( ROOT_DIR  . '/' . $asset . '/' . $file, $context );
                    }
                    $src .= '<link rel="stylesheet" type="text/css" href="' . ASSET_URL . '/' . $asset . '/' . $file . '" />';
                }
            } else {*/
            if ( !$this->IsExternalLink( $css ) ) {
                $css = str_replace(ROOT_DIR, '', $css);
            }
                $src .= '<link rel="stylesheet" type="text/css" href="' . $css . '" />'."\n";
            //}
        }
        return $src;
    }

    private function fixCssIncludes( $content, $path, $real_path )
    {
        $content = preg_replace_callback(
            '#([;\s:]*(?:url|@import)\s*\(\s*)(\'|"|)(.+?)(\2)\s*\)#si',
            function ($matches) use ($path, $real_path) {
                return $matches[1].$this->replaceUrlCSS($matches[3], $matches[2], addslashes($path), $real_path).")";
            },
            $content
        );

        $content = preg_replace_callback(
            '#(\s*@import\s*)([\'"])([^\'"]+)(\2)#si',
            function ($matches) use ($path, $real_path) {
                return $matches[1].$this->replaceUrlCSSreplaceUrlCSS($matches[3], $matches[2], addslashes($path), $real_path);
            },
            $content
        );

        $content = preg_replace_callback(
            '#((\/\*\#\ssourceMappingURL=(.*))\*\/)#si',
            function ($matches) use ($path, $real_path) {
                $this->replaceUrlCss($matches[3], '', addslashes($path), $real_path);
                return '/*# sourceMappingURL=' . $matches[3] . ' */';
            },
            $content
        );
        return $content;
    }

    public function addFileUrl( $url )
    {
        return $url;
        $path = dirname($url);
        $url = str_replace( $path . '/', '', $url );
        //return $this->replaceUrlCss( $url, '',  ASSET_DIR . '/' . mb_substr( md5( $path ), 0, 7 ), $path );
    }

    private function replaceUrlCss($url, $quote, $path, $real_path)
    {
        if (
            mb_strpos($url, "://") !== false
            || mb_strpos($url, "data:") !== false
            || mb_substr($url, 0, 1) == "#"
        ) {
            return $quote . $url . $quote;
        }

        Directory::createDirectory( ROOT_DIR . $path );
        $url = trim(stripslashes($url), "'\" \r\n\t");
        if (mb_substr($url, 0, 1) == '/') {
            return $quote . $url . $quote;
        }

        if ( mb_substr($url, 0, 2) == '..' ) {
            $url = mb_substr($url, 2);
            $real_path = dirname( $real_path );
        }

        $url = parse_url( $url );
        $url = $url['path'];

        if ( mb_substr($url, 0, 1) == '.' ) $url = mb_substr($url, 1);
        $url = '/' . $url;
        $url = str_replace( '//', '/', $url );
        $path = $quote . $path . '/' . $url . $quote;
        $path = str_replace( '//', '/', $path );

        if ( File::isFileExists( $real_path . $url ) ) {
            $path = str_replace('\'', '', $path );
            $path = str_replace('"', '', $path );
            if (
                !File::isFileExists( ROOT_DIR . $path ) ||
                (
                    File::isFileExists( ROOT_DIR . $path ) &&
                    File::hasFile( ROOT_DIR . $path ) != File::hasFile( $real_path . $url )
                )
            ) {
                $_path = explode( '/', $path );
                unset( $_path[ count( $_path ) - 1 ] );
                $_path = implode( '/', $_path );
                Directory::createDirectory( ROOT_DIR . $_path );
                copy( $real_path . $url, ROOT_DIR . $path );
            }
        }
        $path = str_replace( WEB_DIR, '', $path );
        return $path;
    }

    public function getJs()
    {
        $src = '';
        ksort( $this->js );
        foreach ( $this->js as $js ) {
            /*if ( !$this->IsExternalLink( $js )  ) {
                $thema = Reagordi::getInstance()->getConfig()->get( 'theme', 'site' );
                if ( RESPONSE_ADMIN ) $thema = Reagordi::getInstance()->getConfig()->get( 'theme', 'admin' );
                $path = $js;
                if ( File::isFileExists( $path ) ) {
                    $asset = substr( File::hasFile( $path ), 0, 7 );
                    $file = explode( '/', $path );
                    $file = $file[count( $file ) - 1];
                    //$file = substr( File::hasFile( $path ), 7, 7 ) . '.js';
                    if ( !File::isFileExists( ROOT_DIR  . '/' . $asset . '/' . $file ) ) {
                        $context = file_get_contents( $path );
                        if ( Reagordi::getInstance()->getConfig()->get( 'optimize', 'gzip_js' ) ) {
                            $context = Optimize::js( $context );
                        }
                        Directory::createDirectory( ROOT_DIR  . '/' . $asset );
                        file_put_contents( ROOT_DIR  . '/' . $asset . '/' . $file, $context );
                    }
                    $src .= '<script src="' . ASSET_URL . '/' . $asset . '/' . $file . '"></script>';
                }
            } else {*/
            if ( !$this->IsExternalLink( $js ) ) {
                $js = str_replace(ROOT_DIR, '', $js);
            }
                $src .= '<script src="' . $js . '"></script>'."\n";
            //}
        }
        return $src;
    }

    /**
     * Экземпляр класса
     *
     * @return Asset
     */
    public static function getInstance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new Asset();
        }

        return self::$instance;
    }
}
