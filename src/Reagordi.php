<?php

use Reagordi\Framework\Base\Applicaiton;
use Reagordi\Framework\Config\Options;
use Reagordi\Framework\Config\Config;
use Reagordi\Framework\Base\Security;
use Reagordi\Framework\Base\Context;

class Reagordi
{
  /**
   * Экземпляр класса Reagordi
   *
   * @var Reagordi
   */
  public static $app;

  /**
   * Экземпляр класса Application
   *
   * @var Application
   */
  public $applicaiton;

  /**
   * Экземпляр класса Context
   *
   * @var Context
   */
  public $context;

  /**
   * Экземпляр класса Config
   *
   * @var Config
   */
  public $config;

  /**
   * Экземпляр класса Options
   *
   * @var Options
   */
  public $options;

  /**
   * Безопасность
   *
   * @var Security
   */
  public $security;

  /**
   * Reagordi constructor.
   */
  protected function __construct()
  {
    $this->applicaiton = Applicaiton::getInstance();
    $this->context = Context::getCurrent();
    $this->config = Config::getInstance();
    $this->options = Options::getInstance();
    $this->options = Options::getInstance();
    $this->security = Security::getInstance();
  }

  /**
   * Возврат экземпляра класса Config
   *
   * @return Config
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Возврат экземпляра класса Options
   *
   * @return Options
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Возврат экземпляра класса Application
   *
   * @return Application
   */
  public function getApplication()
  {
    return $this->applicaiton;
  }

  /**
   * Возврат экземпляра класса Context
   *
   * @return Context
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Returns current instance of the Reagordi.
   *
   * @return Reagordi
   */
  public static function getInstance()
  {
    if (self::$app === null) {
      self::$app = new Reagordi();
    }
    return self::$app;
  }
}
