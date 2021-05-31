<?php

namespace Reagordi\Framework\Base;

use Reagordi;

class Security
{
    protected static $obj = null;

    public function generatePasswordHash(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function validatePassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function encryptByKey(string $plaintext): string
    {
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, Reagordi::$app->options->get('components', 'request', 'cookieValidationKey'), $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, Reagordi::$app->options->get('components', 'request', 'cookieValidationKey'), $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
        return $ciphertext;
    }

    public function decryptByKey(string $ciphertext): string
    {
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $plaintext = @openssl_decrypt($ciphertext_raw, $cipher, Reagordi::$app->options->get('components', 'request', 'cookieValidationKey'), $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, Reagordi::$app->options->get('components', 'request', 'cookieValidationKey'), $as_binary = true);
        if (@hash_equals($hmac, $calcmac)) {
            return $plaintext;
        }
        return '';
    }

    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new Security();
        }
        return self::$obj;
    }
}
