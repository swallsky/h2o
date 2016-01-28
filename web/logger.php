<?php
/**
 * 日志记录器
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
use H2O;
class Logger implements H2O\base\Logger
{
	/**
	 * 写入日志
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionWrite($message,$files)
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
		$logfile = APP_RUNTIME.DS.'web'.DS.date('Ymd').'_exception.log'; //异常日志文件
		$content = $log.PHP_EOL.PHP_EOL; //内容
		H2O\helpers\File::write($logfile,$content);//写入日志信息
	}
	/**
	 * 调试显示
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionDebug($message,$files)
	{
		header("Content-type: text/html; charset=utf-8");
		echo '<div><b>Message:</b><span style="color:red;">'.$message.'</span></div>';
		echo '<div><b>Stack trace:</b></div>';
		$lspt=3;$fi=0;
		foreach($files as $f=>$lines){
			$sfile = file($f); //读取文件信息
			$trow = count($sfile); //该文件总行数
			echo '<div><span style="color:red;">'.$f.'</span><b style="margin-left:10px;">Lines:'.join('、',$lines).'</b></div>';
			if($fi===0){
				foreach($lines as $line){
					$min = $line-$lspt; $max = $line+$lspt;
					$min = $min<0?0:$min;
					$max = $max>$trow?$trow:$max;
					for($l=$min;$l<=$max;$l++){
						echo '<div '.($l==$line?'style="color:red;"':'').'>'.$l.':'.$sfile[$l-1].'</div>';
					}
				}
			}
			$fi++;
		}
	}
}