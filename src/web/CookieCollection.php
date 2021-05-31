<?php


namespace Reagordi\Framework\Web;


use Reagordi;

class CookieCollection
{
    private static $obj = null;
    private $cookie;

    private function __construct()
    {
        $cookie = $_COOKIE;
        $this->cookie = [];
        if (isset($cookie[RG_COOKIE_SID])) $sid = $cookie[RG_COOKIE_SID];
        elseif (isset($this->cookie[RG_COOKIE_SID])) $sid = $this->cookie[RG_COOKIE_SID];
        else $sid = '';
        if (isset($cookie[RG_COOKIE_LANG])) $lang = $cookie[RG_COOKIE_LANG];
        elseif (isset($this->cookie[RG_COOKIE_LANG])) $lang = $this->cookie[RG_COOKIE_LANG];
        else $lang = '';
        foreach ($cookie as $name => $value) {
            $name = str_replace(RG_COOKIE_PREF, '', $name);
            $this->cookie[$name] = Reagordi::getInstance()->security->decryptByKey($value);
        }
        $this->cookie[str_replace(RG_COOKIE_PREF, '', RG_COOKIE_SID)] = $sid;
        $this->cookie[str_replace(RG_COOKIE_PREF, '', RG_COOKIE_LANG)] = $lang;
        unset($this->cookie[RG_COOKIE_SID], $this->cookie[RG_COOKIE_LANG]);
    }

    public function getList()
    {
        return $this->cookie;
    }

    public function getValue(string $name, $default = null)
    {
        if (isset($this->cookie[$name])) return $this->cookie[$name];
        return $default;
    }

    public function get(string $name)
    {
        return $this->getValue($name);
    }

    public function __get($name)
    {
        if (isset($this->cookie[$name])) return $this->cookie[$name];
        return null;
    }

    public function has(string $name)
    {
        return isset($this->cookie[$name]) ? true : false;
    }

    public function add(string $name, string $value, $expires = false)
    {
        $this->cookie[$name] = $value;
        if ($expires) $expires = time() + ($expires * 86400);
        if (\Reagordi::$app->options->get('components', 'request', 'enableCookieValidation')) {
            $value = Reagordi::$app->security->encryptByKey($value);
        }
        $_COOKIE[RG_COOKIE_PREF . $name] = $value;
        if (Reagordi::$app->options->get('components', 'request', 'onlySSL')) setcookie(RG_COOKIE_PREF . $name, $value, $expires, '/', DOMAIN, true, true);
        else setcookie(RG_COOKIE_PREF . $name, $value, $expires, '/', DOMAIN, null, true);
        return true;
    }

    public function remove($name)
    {
        unset($_COOKIE[RG_COOKIE_PREF . $name], $this->cookie[$name]);
        $expires = time() - 86400;
        if (Reagordi::$app->options->get('components', 'request', 'onlySSL')) setcookie(RG_COOKIE_PREF . $name, '', $expires, '/', DOMAIN, true, true);
        else setcookie(RG_COOKIE_PREF . $name, '', $expires, '/', DOMAIN, null, true);
        return true;
    }

    public function __unset($name)
    {
        unset($_COOKIE[$name], $this->cookie[$name]);
        $expires = time() - ($expires * 86400);
        if (Reagordi::$app->options->get('components', 'request', 'onlySSL')) setcookie($name, '', $expires, '/', DOMAIN, true, true);
        else setcookie($name, '', $expires, '/', DOMAIN, null, true);
        return true;
    }

    public function __toString()
    {
        return $this->cookie;
    }

    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new CookieCollection();
        }
        return self::$obj;
    }
}
