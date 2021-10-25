<?php

$ret = [];
$ctrl = new \SimpleSAML\Module\svgtasession\ctrl();
try{
	$ret['success'] = $ctrl->ctrAll();
	$source = $ctrl->getSource();
	$useToken = $ctrl->getUseToken();
	$sessionId = $ctrl->getSessionId();
	$ret['data'] = \SimpleSAML\Module\svgtasession\ses::getAttributes($sessionId, $source, $useToken);
}catch(\Exception $e){
	$ret['success'] = false;
	$ret['error'] = $e->getMessage();
}
if($ret['success'])
	header("HTTP/1.1 200 Ok");

header('Content-Type: application/json; charset=utf-8');
echo json_encode($ret, JSON_PRETTY_PRINT);
