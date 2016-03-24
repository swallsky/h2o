<?php

/**
 * openim消息结构体
 * @author auto create
 */
class ImMsg
{
	
	/** 
	 * 发送的消息内容。根据不同消息类型，传不同的值。0(文本消息):填消息内容字符串。1(图片):base64编码的jpg或gif文件。2(语音):base64编码的amr文件。8(地理位置):经纬度，格式如 111,222
	 **/
	public $context;
	
	/** 
	 * 发送方appkey
	 **/
	public $from_appkey;
	
	/** 
	 * 消息发送者
	 **/
	public $from_user;
	
	/** 
	 * json map，媒体信息属性。根据msgtype变化。0(文本):填空即可。 1(图片):需要图片格式，{"type":"jpg"}或{"type":"gif"}。   2(语音): 需要文件格式和语音长度信息{"type":"amr","playtime":5}
	 **/
	public $media_attr;
	
	/** 
	 * 消息类型。0:文本消息。1:图片消息，只支持jpg、gif。2:语音消息，只支持amr。8:地理位置信息。
	 **/
	public $msg_type;
	
	/** 
	 * 接收方appkey，默认本app，跨app发送时需要用到
	 **/
	public $to_appkey;
	
	/** 
	 * 消息接受者
	 **/
	public $to_users;	
}
?>