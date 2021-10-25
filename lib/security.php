<?php
namespace SimpleSAML\Module\svgtasession;

class security{
	private static function getAuthorizationHeader(){
		$headers = null;
		if (isset($_SERVER['Authorization']) OR isset($_SERVER["X-Auth-Token"])) {
			$headers = isset($_SERVER["Authorization"]) ? trim($_SERVER["Authorization"]) : trim($_SERVER["X-Auth-Token"]);
		}else if (isset($_SERVER['HTTP_AUTHORIZATION']) OR isset($_SERVER["HTTP_X_AUTH_TOKEN"])) { 
			$headers = isset($_SERVER["HTTP_AUTHORIZATION"]) ? trim($_SERVER["HTTP_AUTHORIZATION"]) : trim($_SERVER["HTTP_X_AUTH_TOKEN"]);
		}else if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization']) OR isset($requestHeaders['X-Auth-Token'])) {
				$headers = isset($requestHeaders['Authorization']) ? trim($requestHeaders['Authorization']) : trim($requestHeaders['X-Auth-Token']);
			}
		}
		return $headers;
	}
	private static function getBearerToken() {
		$headers = self::getAuthorizationHeader();
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
	private static function getAuthToken() {
		return self::getAuthorizationHeader();
	}
	public static function verifyAuth(){
		$res = (self::getBearerToken()) ? self::getBearerToken() : self::getAuthToken();
		if($res == null)
			return null;
		$authList = \SimpleSAML\Configuration::getConfig('authorizedKeys.php')->toArray();
		if(in_array($res, $authList['keys']))
			return true;
		return null;
	}
}