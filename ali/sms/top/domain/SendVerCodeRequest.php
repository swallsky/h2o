<?php

/**
 * 发送验证码请求
 * @author auto create
 */
class SendVerCodeRequest
{
	
	/** 
	 * appKey
	 **/
	public $app_key;
	
	/** 
	 * 业务类型
	 **/
	public $biz_type;
	
	/** 
	 * 短信内容替换上下文
	 **/
	public $context;
	
	/** 
	 * 设备id
	 **/
	public $device_id;
	
	/** 
	 * 设备级别的发送次数限制
	 **/
	public $device_limit;
	
	/** 
	 * 发送次数限制的时间，单位为秒
	 **/
	public $device_limit_in_time;
	
	/** 
	 * 场景域，比如登录的验证码不能用于注册
	 **/
	public $domain;
	
	/** 
	 * 验证码失效时间，单位为秒
	 **/
	public $expire_time;
	
	/** 
	 * 外部的id
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
	 * 手机号的次数限制
	 **/
	public $mobile_limit;
	
	/** 
	 * 手机号的次数限制的时间
	 **/
	public $mobile_limit_in_time;
	
	/** 
	 * session id
	 **/
	public $session_id;
	
	/** 
	 * session级别的发送次数限制
	 **/
	public $session_limit;
	
	/** 
	 * 发送次数限制的时间，单位为秒
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
	
	/** 
	 * 验证码长度
	 **/
	public $ver_code_length;	
}
?>