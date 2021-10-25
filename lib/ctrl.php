<?php
namespace SimpleSAML\Module\svgtasession;

class ctrl{
	private static $useToken = null;
	private static $source = null;
	private static $sessionId = null;

	public function getUseToken(){
		return self::$useToken;
	}
	public function getSource(){
		return self::$source;
	}
	public function getSessionId(){
		return self::$sessionId;
	}
	public function ctrlAuth(){
		if(!\SimpleSAML\Module\svgtaSession\security::verifyAuth()){
			header("HTTP/1.1 403 Forbidden");
			throw new \Exception('Accès interdit');
		}	
	}
	public function ctrAll(){
		$this->ctrlAuth();
		$this->ctrlMethode();
		$res = $this->getPost();
		if($this->isToken($res))
			return $this->isAuthToken($res['token']);
		else
			return $this->isAuthSesSource($res);
	}
	public function isAuthSesSource($ar){
		self::$source = $ar['source'];
		self::$sessionId = $ar['sessionId'];
		$ret = \SimpleSAML\Module\svgtaSession\ses::isAuthenticated($ar['sessionId'], $ar['source'], false);
		if(!$ret)
			header("HTTP/1.1 401 Unauthorized");
		return $ret;
	}
	public function isAuthToken($token){
		$json = \SimpleSAML\Module\svgtaSession\ses::getToken($token);
		$sessionId = $json['sesId'];
		$sourceAr = $json['auth'];
		$isAuth = false;
		if($sourceAr)
		foreach($sourceAr as $source){
			$isAuth = \SimpleSAML\Module\svgtaSession\ses::isAuthenticated($sessionId, $source, true);
			if($isAuth)
				break;
		}
		self::$source = isset($source) ? $source : null;
		self::$sessionId = $sessionId;
		return $isAuth;
	}
	public function isToken($ar){
		self::$useToken = false;
		if(!$ar['token']){
			if(!$ar['source'] OR !$ar['sessionId']){
				header("HTTP/1.1 401 Unauthorized");
				throw new \Exception('Eléments entrants manquants. Nécessite soit token soit sesId et source');
			}
		}else{
			self::$useToken = true;
		}
		return self::$useToken;
	}
	private function isJsonString($string){
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}
	public function getPost(){
		$source = null;
		$sessionId = null;	
		$token = null;
		$input = file_get_contents("php://input");
		if($this->isJsonString($input))
			$post = json_decode($input, TRUE);
		else
			$post = $_POST;

		if(isset($post["source"]) AND !empty($post["source"])){
			assert(is_string($post["source"]));
			$source = $post["source"];
		}
		if(isset($post["sesId"]) AND !empty($post["sesId"])){
			assert(is_string($post["sesId"]));
			$sessionId = $post["sesId"];
		}
		if(isset($post["token"]) AND !empty($post["token"])){
			assert(is_string($post["token"]));
			$token = $post["token"];
		}

		return [
			'source' => $source,
			'sessionId' => $sessionId,
			'token' => $token,
		];
	}
	public function ctrlMethode(){
		$request_method = $_SERVER["REQUEST_METHOD"];
		if(!($request_method == 'POST')){
			header("HTTP/1.1 405 Method Not Allowed");
			throw new \Exception('Seulement POST autorisé');
		}
	}
}