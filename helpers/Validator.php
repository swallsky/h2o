<?php
/**
 * 验证助手类
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class Validator
{
	/**
	 * 是否为空值
	 * @param string $str 需要验证的值
	 * @return 如果为空，则返回true,否则返回false
	 */
	public static function isEmpty($str)
	{
		$str = trim($str);
		return $str===''?true:false;
	}
	/**
	 * 验证整数 支持字符整数，例如 '11'
	 * @param string $str 需要验证的值
	 * @return 如果为整数，则返回true,否则返回false
	 */
	public static function isInt($str) 
	{
		$pattern = '/^\s*[+-]?\d+\s*$/';
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * 验证浮点
	 * @param string $str 需要验证的值
	 * @return 如果为浮点，则返回true,否则返回false
	 */
	public static function isFloat($str)
	{
		$pattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * 邮箱验证
	 * @param string $str 需要验证的值
	 * @return 如果为邮箱，则返回true,否则返回false
	 */
	public static function isEmail($str)
	{
		$pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * 手机验证
	 * @param string $str 需要验证的值
	 * @return 如果为手机号码，则返回true,否则返回false
	 */
	public static function isMobile($str)
	{
		$pattern = "/^1[3578]{1}[0-9]{9}$/";
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * URL验证，纯网址格式
	 * @param string $str 需要验证的值
	 * @return 如果为Url，则返回true,否则返回false
	 */
	public static function isUrl($str)
	{
		$pattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * 验证中文
	 * @param string $str 需要验证的值
	 * @param string $charset 编码（默认utf-8,支持gb2312）
	 */
	public static function isChinese($str, $charset = 'utf-8')
	{
		$pattern = (strtolower ( $charset ) == 'gb2312') ? "/^[" . chr ( 0xa1 ) . "-" . chr ( 0xff ) . "]+$/" : "/^[x{4e00}-x{9fa5}]+$/u";
		return preg_match($pattern,$str)?true:false;
	}
	/**
	 * 验证长度
	 * @param string $str 需要验证的值
	 * @param int $type (方式，默认min <= $str <= max)
	 * @param int $min 最小值;$max,最大值;
	 * @param string $charset 字符
	 */
	public static function length($str, $type = 3, $min = 0, $max = 0, $charset = 'utf-8')
	{
		$len = mb_strlen($str,$charset);
		switch ($type) {
			case 1 : // 只匹配最小值
				return ($len >= $min) ? true : false;
				break;
			case 2 : // 只匹配最大值
				return ($max >= $len) ? true : false;
				break;
			default : // min <= $str <= max
				return (($min <= $len) && ($len <= $max)) ? true : false;
		}
	}
	/**
	 * 安全验证
	 * @param string $str 需要验证的值
	 * @param int $length        	
	 * @return boolean
	 */
	public static function isSafe($str, $minLen = 6, $maxLen = 16)
	{
		$match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{' . $minLen . ',' . $maxLen . '}$/';
		$v = trim($str);
		if (empty($v))
			return false;
		return preg_match($match,$v);
	}
}