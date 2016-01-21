<?php
/**
 * 自定义异常处理的类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
class Exception extends \Exception
{
	/**
	 * @param object $e 异常对象
	 */
	public function __construct($e)
	{
		$msg = date('Y-m-d H:i:s').PHP_EOL."\tFile:".$this->getFile().PHP_EOL."\tLine:".$this->getLine().PHP_EOL."\t".$tag.": ".$msg.PHP_EOL;//异常信息
		echo $msg;
		exit();
	}
}
?>