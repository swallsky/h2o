<?php
/**
 * 日志记录器接口定义
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
interface Logger
{
	/**
	 * 异常写入日志
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionWrite($message,$files);
	/**
	 * 异常调试显示
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionDebug($message,$files);
	/**
	 * 写入debugger日志
	 * @param unknown_type $data
	 */
	public function debugger($data);
}