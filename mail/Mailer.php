<?php
/**
 * 邮件发送
 * @category   H2O
 * @package    mail
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\mail;
class Mailer
{
	/**
	 * @var object 发送主体对象
	 */
	private $_transport;
	/**
	 * @var array 邮箱配置信息
	 */
	private $_config = [];
	/**
	 * 初始化应用
	 * @param string $tag 邮箱配置标识
	 * config example
	 [
	 	'mailer'	=>	[
	 		'default'	=>	[
	 			'host'				=>	'smtp.163.com',
	 			'port'				=>	25,
	 			'from'				=>	['username@163.com'=>'测试公司'],
	 			'username'		=>	'username@163.com',
	 			'password'		=>	'password'
	 		],
	 		'test'	=>	[
	 			'host'				=>	'smtp.163.com',
	 			'port'				=>	25,
	 			'from'				=>	['username@163.com'=>'测试公司'],
	 			'username'		=>	'username@163.com',
	 			'password'		=>	'password'
	 		]
	 	]
	 ]
	 */
	public function __construct($tag = 'default')
	{
		$mailconf = \H2O::getAppConfigs('mailer');
		if(empty($mailconf)){
			throw new \Exception('Mailer config params is error');
		}else{
			if(isset($mailconf[$tag])){
				$this->_config = $mailconf[$tag];
				$this->_transport = $this->_getTransport($this->_config);
			}else{
				throw new \Exception('Mailer config params is error: '.$tag);
			}
		}
	}
	/**
	 * 返回初始化发送者
	 */
	private function _getTransport()
	{
		$transport = \Swift_SmtpTransport::newInstance($this->_config['host'],$this->_config['port']);
 		$transport->setUsername($this->_config['username']);
		$transport->setPassword($this->_config['password']);
		return  \Swift_Mailer::newInstance($transport);
	}
	/**
	 * 发送邮箱
	 * @param array $toUser 接收邮箱  ['whoever@163.com' => 'Mr.Right', 'whoever@qq.com' => 'Mr.Wrong']
	 * @param string $subject 标题
	 * @param string $content 邮箱内容
	 * @throws \Exception
	 */
	public function send($toUser = [],$subject = '',$content = '')
	{
		$message = \Swift_Message::newInstance();
		$message->setFrom($this->_config['from']);
		$message->setTo($toUser);
		$message->setSubject($subject);
		$message->setBody($content, 'text/html', 'utf-8');
		try{
			$this->_transport->send($message);
		}catch (\Swift_ConnectionException $e){
			throw new \Exception('There was a problem communicating with SMTP: ' . $e->getMessage());
		}
	}
}