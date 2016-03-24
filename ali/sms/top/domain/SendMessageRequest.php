<?php

/**
 * 发送短信请求
 * @author auto create
 */
class SendMessageRequest
{
	
	/** 
	 * app key
	 **/
	public $app_key;
	
	/** 
	 * 业务类型
	 **/
	public $biz_type;
	
	/** 
	 * 模板上下文
	 **/
	public $context;
	
	/** 
	 * 设备id
	 **/
	public $device_id;
	
	/** 
	 * 设备级别次数限制
	 **/
	public $device_limit;
	
	/** 
	 * 时间，单位秒
	 **/
	public $device_limit_in_time;
	
	/** 
	 * 业务域
	 **/
	public $domain;
	
	/** 
	 * 外部id
	 **/
	public $external_id;
	
	/** 
	 * long型模板id
	 **/
	public $long_template_id;
	
	/** 
	 * 手机号
	 **/
	public $mobile;
	
	/** 
	 * 手机号限制
	 **/
	public $mobile_limit;
	
	/** 
	 * 时间，单位秒
	 **/
	public $mobile_limit_in_time;
	
	/** 
	 * sessionId
	 **/
	public $session_id;
	
	/** 
	 * session级别次数限制
	 **/
	public $session_limit;
	
	/** 
	 * 时间，单位秒
	 **/
	public $session_limit_in_time;
	
	/** 
	 * 签名id
	 **/
	public $signature_id;
	
	/** 
	 * 模板id
	 **/
	public $template_id;
	
	/** 
	 * 用户id
	 **/
	public $user_id;	
}
?>