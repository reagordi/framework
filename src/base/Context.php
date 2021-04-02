<?php
/**
 * MediaLife Framework
 *
 * @package reagordi/framework
 * @subpackage system
 * @author Sergej Rufov <support@freeun.ru>
 */

namespace Reagordi\Framework\Base;

use Reagordi\Framework\Tools\ArrayToObject;
use Reagordi\Framework\Web\Components;
use Reagordi\Framework\Config\Config;
use Reagordi\Framework\Web\Languages;
use Reagordi\Framework\Web\View;
use Reagordi;

class Context
{
    /** @var Application */
    public $application;

    /** @var Request */
    public $request;

    /** @var Server */
    public $server;

    /** @var Session */
    public $session;

    /** @var Languages */
    public $i18n;

    /** @var Components */
    public $components;

    /** @var View */
    public $view;

    /** @var CacheManager */
    public $cache;

    private $document = [];

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->application = Applicaiton::getInstance();
        $this->server = new Server();
        $this->request = new Request();
        $this->session = new Session();
        $this->i18n = Languages::getInstance();
        $this->components = Components::getInstance();
        $this->view = View::getInstance();
    }

    public function getLanguages()
    {
        return $this->i18n;
    }

    /**
	 * Returns request object of the context.
	 *
	 * @return HttpRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Returns server object of the context.
	 *
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

    /**
     * Returns server object of the context.
     *
     * @return Server
     */
    public function getSession()
    {
        return $this->session;
    }

	/**
	 * Returns backreference to Application.
	 *
	 * @return Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

    /**
     * Вывод шапки сайта
     */
    public function getHead()
    {
        $content = '';
        if ( !isset( $this->document['title'] ) )
            $this->document['title'] = Config::getInstance()->get( 'site_name' );
        if ( !isset( $this->document['robots'] ) )
            $this->document['robots'] = Config::getInstance()->get( 'robots_access' );
        $content .= '<meta charset="utf-8" />'."\n";
        $content .= '<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width" />'."\n";
        if ( isset( $this->document['description'] ) )
            $content .= '<meta name="description" content="' . trim( strip_tags( $this->document['description'] ) ) . '" />'."\n";

        // Meta google
        $content .= '<meta itemprop="name" content="' . $this->document['title'] . '"/>'."\n";
        if ( isset( $this->document['description'] ) )
            $content .= '<meta itemprop="description" content="' . trim( strip_tags( $this->document['description'] ) ) . '"/>'."\n";
        if ( isset( $this->document['seo_image'] ) )
            $content .= '<meta itemprop="image" content="' . $this->document['seo_image'] . '"/>'."\n";

        // Twitter meta
        $content .= '<meta name="twitter:site" content="' . Config::getInstance()->get( 'site_name' ) . '"/>'."\n";
        $content .= '<meta name="twitter:title" content="' . $this->document['title'] . '"/>'."\n";
        if ( isset( $this->document['description'] ) )
            $content .= '<meta name="twitter:description" content="' . $this->document['description'] . '"/>'."\n";
        if ( isset( $this->document['seo_image'] ) )
            $content .= '<meta name="twitter:image:src" content="' . $this->document['seo_image'] . '"/>'."\n";
        $content .= '<meta name="twitter:domain" content="' . $this->getServer()->getHttpHost() . '"/>'."\n";

        // Meta Og
        $content .= '<meta property="og:site_name" content="' . Reagordi::$app->config->get( 'site_name' ) . '"/>'."\n";
        $content .= '<meta property="og:title" content="' . $this->document['title'] . '" />'."\n";
        if ( isset( $this->document['description'] ) )
            $content .= '<meta property="og:description" content="' . trim( strip_tags( $this->document['description'] ) ) . '" />'."\n";
        if ( isset( $this->document['seo_image'] ) )
            $content .= '<meta property="og:image" content="' . $this->document['seo_image'] . '"/>'."\n";

        // Robots
        if ( isset( $this->document['robots'] ) && $this->document['robots'] != 'index,follow' )
            $content .= '<meta name="robots" content="' . $this->document['robots'] . '" />'."\n";

        // Author info
        if ( Config::getInstance()->get( 'show_author' ) ) {
            $content .= '<meta name="generator" content="Reagordi Framework" />'."\n";
            $content .= '<meta name="author" content="Reagordi Framework" />'."\n";
            $content .= '<meta name="copyright" content="Reagordi Framework (c) '.date('Y').'" />'."\n";
            $content .= '<meta http-equiv="reply-to" content="support@reagordi.com" />'."\n";
        }
        $content .= '<title>' . $this->document['title'] . '</title>'."\n";
        $content .= '<!--[Reagordi Style]-->';
        $lang = LANGUAGE_ID;
        $api_url = HOME_URL . '/' . Reagordi::$app->options->get( 'url', 'api_path' );
        $sid = isset( $_COOKIE[RG_COOKIE_SID] ) ? $_COOKIE[RG_COOKIE_SID]: Reagordi::$app->context->session->sid;
        $content .= <<<_HTML
<script>
var reagordi = {
    lang: '{$lang}',
    sid: '{$sid}',
    api_url: '{$api_url}'
};
</script>
_HTML;

        echo $content;
    }

    public function getFooter()
    {
        $content = '';
        $content .= '<!--[Reagordi Js]-->';
        echo $content;
    }

    /**
     * Задает заголовок страницы
     *
     * @param string $title Заголовок страницы
     */
    public function setTitle( $title )
    {
        $this->document['title'] = $title;
    }

    /**
     * Задает описание страницы
     *
     * @param string $description Описание страницы
     */
    public function setDescription( $description )
    {
        $this->document['description'] = $description;
    }

    /**
     * Задает права индексировать ли сайт
     *
     * @param string $robots Задает права индексировать ли сайт
     */
    public function setRobots( $robots )
    {
        $this->document['robots'] = $robots;
    }

    /**
     * Задает картунку страницы для соц.сетей
     *
     * @param string $seo_image Путь до изображения
     */
    public function setSeoImage( $seo_image )
    {
        $this->document['seo_image'] = $seo_image;
    }

    /**
	 * Static method returns current instance of context.
	 *
	 * @static
	 * @return Context
	 */
	public static function getCurrent()
	{
		$application = Applicaiton::getInstance();
		return $application->getContext();
	}
}
