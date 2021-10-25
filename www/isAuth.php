<?php

$ret = [];
$ctrl = new \SimpleSAML\Module\svgtaSession\ctrl();
try{
	$ret['success'] = $ctrl->ctrAll();
}catch(\Exception $e){
	$ret['success'] = false;
	$ret['error'] = $e->getMessage();
}
if($ret['success'])
	header("HTTP/1.1 200 Ok");
//else
//	header("HTTP/1.1 401 Unauthorized");

header('Content-Type: application/json; charset=utf-8');
echo json_encode($ret, JSON_PRETTY_PRINT);
