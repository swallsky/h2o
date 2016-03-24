<?php

/**
 * 自定义消息内容
 * @author auto create
 */
class CustMsg
{
	
	/** 
	 * apns推送的附带数据。
	 **/
	public $apns_param;
	
	/** 
	 * apns推送时，里面的aps结构体json字符串，aps.alert为必填字段。本字段为可选，若为空，则表示不进行apns推送
	 **/
	public $aps;
	
	/** 
	 * 发送的自定义数据，sdk默认无法解析消息，该数据需要客户端自己解析
	 **/
	public $data;
	
	/** 
	 * 发送方appkey
	 **/
	public $from_appkey;
	
	/** 
	 * 发送方userid
	 **/
	public $from_user;
	
	/** 
	 * 客户端最近消息里面显示的消息摘要
	 **/
	public $summary;
	
	/** 
	 * 接收方appkey，默认是发送方appkey，如需跨域发送，需要走审批流程
	 **/
	public $to_appkey;
	
	/** 
	 * 接受者userid列表，单次发送用户数小于100
	 **/
	public $to_users;	
}
?>