<?php

namespace Crockett95\Duo;

use \Config;
use \View;

use \Crockett95\Duo\DuoException;

class Duo
{

    /**
     *
     */
    const DUO_PREFIX = "TX";

    /**
     *
     */
    const APP_PREFIX = "APP";

    /**
     *
     */
    const AUTH_PREFIX = "AUTH";

    /**
     *
     */
    const DUO_EXPIRE = 300;

    /**
     *
     */
    const APP_EXPIRE = 3600;

    /**
     *
     */
    const IKEY_LEN = 20;

    /**
     *
     */
    const SKEY_LEN = 40;

    /**
     *
     */
    const AKEY_LEN = 40; // if this changes you have to change ERR_AKEY

    /**
     *
     */
    const ERR_USER = 'ERR|The username passed to sign_request() is invalid.';

    /**
     *
     */
    const ERR_IKEY = 'ERR|The Duo integration key passed to sign_request() is invalid.';

    /**
     *
     */
    const ERR_SKEY = 'ERR|The Duo secret key passed to sign_request() is invalid.';

    /**
     *
     */
    const ERR_AKEY = 'ERR|The application secret key passed to sign_request() must be at least 40 characters.';

    /**
     * Duo AKEY setting for this instance
     *
     * @var     string  $aKey
     */
    protected $akey;

    /**
     * Duo IKEY setting for this instance
     *
     * @var     string  $iKey
     */
    protected $ikey;

    /**
     * Duo SKEY setting for this instance
     *
     * @var     string  $sKey
     */
    protected $skey;

    /**
     * Duo HOST setting for this instance
     *
     * @var     string  $host
     */
    protected $host;

    /**
     *
     */
    public function __construct($aKey = null, $iKey = null, $sKey = null, $host = null)
    {
        $this->aKey = $aKey ?: $this->getConfig('keys.AKEY');
        $this->iKey = $iKey ?: $this->getConfig('keys.IKEY');
        $this->sKey = $sKey ?: $this->getConfig('keys.SKEY');
        $this->host = $host ?: $this->getConfig('keys.HOST');
    }

    /**
     *
     */
    public function sign($username, $time = null)
    {
        return self::signRequest(
            $this->ikey,
            $this->skey,
            $this->akey,
            $username,
            $time
        );
    }

    /**
     *
     */
    public function verify($sig_response, $time = null)
    {
        return self::verifyResponse(
            $this->ikey,
            $this->skey,
            $this->akey,
            $sig_response,
            $time
        );
    }

    public function confirmationForm($username, $endpoint = null, $time = null)
    {
        $sig = $this->sign($username, $time);

        return View::make($this->getConfig('view'))
            ->with('sig_request', $sig)
            ->with('host', $this->host);
    }

    /**
     * Convenience method to get the configuration for this package
     */
    protected function getConfig($key)
    {
        return Config::get("duo::$key");
    }

    /**
     *
     */
    private static function signVals($key, $vals, $prefix, $expire, $time=NULL)
    {
        $exp = ($time ? $time : time()) + $expire;
        $val = $vals . '|' . $exp;
        $b64 = base64_encode($val);
        $cookie = $prefix . '|' . $b64;
        $sig = hash_hmac("sha1", $cookie, $key);
        return $cookie . '|' . $sig;
    }

    /**
     *
     */
    private static function parseVals($key, $val, $prefix, $time=NULL)
    {
        $ts = ($time ? $time : time());
        list($u_prefix, $u_b64, $u_sig) = explode('|', $val);
        $sig = hash_hmac("sha1", $u_prefix . '|' . $u_b64, $key);
        if (hash_hmac("sha1", $sig, $key) != hash_hmac("sha1", $u_sig, $key)) {
            return null;
        }
        if ($u_prefix != $prefix) {
            return null;
        }
        list($user, $ikey, $exp) = explode('|', base64_decode($u_b64));
        if ($ts >= intval($exp)) {
            return null;
        }
        return $user;
    }

    /**
     *
     */
    public static function signRequest($ikey, $skey, $akey, $username, $time = null)
    {
        if (!isset($username) || strlen($username) == 0){
            return self::ERR_USER;
        }
        if (!isset($ikey) || strlen($ikey) != self::IKEY_LEN) {
            return self::ERR_IKEY;
        }
        if (!isset($skey) || strlen($skey) != self::SKEY_LEN) {
            return self::ERR_SKEY;
        }
        if (!isset($akey) || strlen($akey) < self::AKEY_LEN) {
            return self::ERR_AKEY;
        }
        $vals = $username . '|' . $ikey;
        $duo_sig = self::signVals($skey, $vals, self::DUO_PREFIX, self::DUO_EXPIRE, $time);
        $app_sig = self::signVals($akey, $vals, self::APP_PREFIX, self::APP_EXPIRE, $time);
        return $duo_sig . ':' . $app_sig;
    }

    /**
     *
     */
    public static function verifyResponse($ikey, $skey, $akey, $sig_response, $time = null)
    {
        list($auth_sig, $app_sig) = explode(':', $sig_response);
        $auth_user = self::parseVals($skey, $auth_sig, self::AUTH_PREFIX, $time);
        $app_user = self::parseVals($akey, $app_sig, self::APP_PREFIX, $time);
        if ($auth_user != $app_user) {
            return null;
        }
        return $auth_user;
    }

}
