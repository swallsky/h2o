<?php
/**
 * 日志记录器
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O;
class Logger implements H2O\base\Logger
{
	/**
	 * 日志显示格式
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 * @return 返回命令行下的格式
	 */
	private function _exceptionLog($message,$files)
	{
		$log = 'Date:'.date('Y-m-d H:i:s').PHP_EOL;
		$log .= 'Message:'.$message.PHP_EOL;
		$log .= 'Stack trace:'.PHP_EOL;
		$lspt=3;$fi=0;
		foreach($files as $f=>$lines){
			$sfile = file($f); //读取文件信息
			$trow = count($sfile); //该文件总行数
			$log .= $f.' Lines:'.join('、',$lines).PHP_EOL;
			if($fi===0){
				foreach($lines as $line){
					$min = $line-$lspt; $max = $line+$lspt;
					$min = $min<0?0:$min;
					$max = $max>$trow?$trow:$max;
					for($l=$min;$l<=$max;$l++){
						$log .= ($l==$line?'**':'').$l.':'.$sfile[$l-1];
					}
				}
			}
			$fi++;
		}
		return $log.PHP_EOL;
	}
	/**
	 * 写入日志
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionWrite($message,$files)
	{
		$logfile = APP_RUNTIME.DS.'web'.DS.'exception'.DS.date('Ymd').'.log'; //异常日志文件
		$content = $this->_exceptionLog($message, $files); //内容
		H2O\helpers\File::write($logfile,$content);//写入日志信息
	}
	/**
	 * 调试显示
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionDebug($message,$files)
	{
		echo $this->_exceptionLog($message, $files);
	}
	/**
	 * 写入debugger日志
	 * @param array $data 消耗信息
	 */
	public function debugger($data)
	{
		$request = \H2O::getContainer('request');
		$log = 'Uri:'.$request->getRequestUri().PHP_EOL;
		$log .= 'Date:'.date('Y-m-d H:i:s').PHP_EOL;
		$log .= 'RunTime:'.$data['runtime'].PHP_EOL;
		$log .= 'Memory:'.$data['memory'].PHP_EOL;
		$logfile = APP_RUNTIME.DS.'web'.DS.'debugger'.DS.date('Ymd').'.log'; //异常日志文件
		$content = $log.PHP_EOL; //内容
		H2O\helpers\File::write($logfile,$content);//写入日志信息
	}
}