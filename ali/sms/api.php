<?php
/**
 * 基于淘宝开放平台的短信发送
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
include(__DIR__."TopSdk.php"); //导入SDK
/**
 * 短信发送
 * @param array $tag 配置信息模板标识 ['default','test']
配置信息
 [
 	'alisms'	=>	[
 		'default'	=>	[
 			'appkey'			=>	'23268950', //密钥
 			'secretKey'	=>	'09003ad8710387cfd0742c9b7fd6aab2', //密钥
 			'tags'	=>	[
 				'test'=>'750_737' //短信模板标识
 			]
 		]
 	]
 ]
 * @param string $m 多手机号 以逗号分隔
 * @param array $data 替换变量
 */
function AliSmsSend($tag,$m,$data)
{
	$c = new \TopClient();
	$smsconf = \H2O::getAppConfigs('alisms');
	if(empty($smsconf)){
		throw new \Exception('Ali SMS config params is error');
	}else{
		if(isset($smsconf[$tag[0]])){
			$config = $smsconf[$tag[0]];
		}else{
			throw new \Exception('Ali SMS config params is error: '.$tag[0]);
		}
	}
	$c->appkey = $config['appkey'];
	$c->secretKey = $config['secretKey'];
	$req = new \OpenSmsSendmsgRequest();
	$smrequest = new \SendMessageRequest();
	$em = explode(',',$m);
	$ids = explode('_',$config['tags'][$tag[1]]);
	$smrequest->template_id=$ids[0]; //模板ID
	$smrequest->signature_id=$ids[1]; //签名ID
	$smrequest->context=json_decode(json_encode($data)); //模板变量替换
	foreach($em as $v){
		// $smrequest->external_id="demo";
		// $smrequest->mobile="18610638306,13520839197";
		$smrequest->mobile=$v;
		// $smrequest->device_limit="123";
		// $smrequest->session_limit="123";
		// $smrequest->device_limit_in_time="123";
		// $smrequest->mobile_limit="123";
		// $smrequest->session_limit_in_time="123";
		// $smrequest->mobile_limit_in_time="123";
		// $smrequest->session_id="demo";
		// $smrequest->domain="demo";
		// $smrequest->device_id="demo";
		$req->setSendMessageRequest(json_encode($smrequest));
		$resp = $c->execute($req);
		return $resp->result->successful;
	}
}