<?php

namespace SimpleSAML\Module\svgtasession;
use SimpleSAML\Session;

class ses{
	public static function getSessionFromRequest(){
		if(class_exists('Session', true))
			return $session = Session::getSessionFromRequest();
		return $session = \SimpleSAML_Session::getSessionFromRequest();
	}
	public static function setToken(){
		$session = self::getSessionFromRequest();
		$sessionId = $session->getSessionId();
		$auth = $session->getAuthorities();
		$ret = [
			'sesId' => $sessionId,
			'auth' => $auth,
		];

		return bin2hex(\SimpleSAML\Utils\Crypto::aesEncrypt(json_encode($ret)));
	}
	public static function getToken($token = null){
		if(!$token)
			return false;
		try{
			$to = @hex2bin($token);
			$t = json_decode(\SimpleSAML\Utils\Crypto::aesDecrypt($to), TRUE);
			return $t;
		}catch(\Throwable $e){
			header("HTTP/1.1 401 Unauthorized");
			return false;
		}
	}
	public static function getSessionId(){
		$session = self::getSessionFromRequest();
		return bin2hex(\SimpleSAML\Utils\Crypto::aesEncrypt($session->getSessionId()));
	}
	public static function getSession($sessionId, $token = null){
		if(!$token)
			$sessionId = \SimpleSAML\Utils\Crypto::aesDecrypt(hex2bin($sessionId));
		$session = self::getSessionFromRequest();
		return $session->getSession($sessionId);
	}
	public static function isAuthenticated($sessionId, $source, $token = null){
		$session = self::getSession($sessionId, $token);
		$as = new \SimpleSAML\Auth\Simple($source, null, $session);
		$ret = $as->isAuthenticated();
		if(!$ret)
			header("HTTP/1.1 401 Unauthorized");
		return $ret;
	}
	public static function getAttributes($sessionId, $source, $token = null){
		$session = self::getSession($sessionId, $token);
		$as = new \SimpleSAML\Auth\Simple($source, null, $session);
		return $as->getAttributes();
	}

}