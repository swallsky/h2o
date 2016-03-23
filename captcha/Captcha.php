<?php
/**
 * 基于Gregwar/Captcha 整合的验证码
 * @category   H2O
 * @package    captcha
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\captcha;
use H2O;
class Captcha
{
	/**
	 * @var object 验证码对象
	 */
	private $_capobj;
	/**
	 * 应用初始化
	 */
	public function __construct()
	{
		$this->_capobj = new \Gregwar\Captcha\CaptchaBuilder();
	}
	/**
	 * 创建验证码信息
	 * @param int $width 宽
	 * @param int $height 高
	 */
	public function build($width = 150, $height = 40)
	{
		$this->_capobj->build($width,$height);
	}
	/**
	 * 返回图像中的验证码文本值
	 */
	public function getValue()
	{
		return $this->_capobj->getPhrase();
	}
	/**
	 * 输出验证码图像
	 */
	public function getImage()
	{
		header('Content-type: image/jpeg');
		$this->_capobj->output();
	}
}