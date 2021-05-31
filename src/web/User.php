<?php


namespace Reagordi\Framework\Web;

use Reagordi;
use Reagordi\Framework\Base\Security;
use Reagordi\Framework\Web\IdentityInterface;

class User
{
    private static $obj = null;

    /**
     * Гость
     *
     * @var bool
     */
    public $isGuest = true;

    private function __construct()
    {
        /*if (Reagordi::$app->context->session->has('identity_id') && Reagordi::$app->context->session->has('identity')) {

        }*/
    }

    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($duration > 0)
            Reagordi::$app->context->request->cookie->add('authkey', $identity->getAuthKey(), $duration);
        $user = $identity::findIdentity($identity->getId());
        if ($user) {
            Reagordi::$app->context->session->set('identity_id', $identity->getId());
            Reagordi::$app->context->session->set('identity', $user);
            return $user;
        }
        return null;
    }

    public static function getInstance()
    {
        if ( self::$obj === null ) {
            self::$obj = new User();
        }
        return self::$obj;
    }
}