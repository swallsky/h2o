<?php
/**
 * 日志记录器
 * @category   H2O
 * @package    web
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\web;
use H2O,H2O\helpers\Stdout;
class Logger implements H2O\base\Logger
{
	/**
	 * 写入日志
	 * @param string $message 错误信息
	 * @param array $files 跟踪文件
	 */
	public function exceptionWrite($message,$files)
	{
		Stdout::title('Exception Log '.date('Y-m-d H:i:s').Stdout::$br.'Message:'.$message);
		$stackfile = [['File','Lines']]; //相关联的文件
		$code = [['The code key parts']]; //关联的第一个文件代码
		$lspt=3; $fi=0;
		foreach($files as $f=>$lines){
			if($fi===0){//输出关键部位的代码结构
				$sfile = file($f); //读取文件信息
				$trow = count($sfile); //该文件总行数
				$code[] = ['File: '.$f.' Lines:' .implode('、',$lines)];
				$framge = Stdout::$br;//代码片断
				foreach($lines as $line){
					$min = $line-$lspt; $max = $line+$lspt;
					$min = $min<0?0:$min;
					$max = $max>$trow?$trow:$max;
					for($l=$min;$l<=$max;$l++){
						$framge .= ($l==$line?'**':'').$l.':'.$sfile[$l-1];
					}
				}
				$code[] = [$framge];
			}else{
				$stackfile[] = [$f,implode('、',$lines)];
			}
			$fi++;
		}
		Stdout::table($code);//代码跟踪
		if(count($stackfile)>1){
			Stdout::table($stackfile);//相关文件
		}
		$logfile = APP_RUNTIME.DS.'web'.DS.'exception'.DS.date('Ymd').'.log'; //异常日志文件
		$content = Stdout::get();; //内容
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
			echo '<div><span style="color:red;">'.$f.'</span><b style="margin-left:10px;">Lines:'.implode('、',$lines).'</b></div>';
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
	/**
	 * 写入debugger日志
	 * @param array $data 消耗信息
	 */
	public function debugger($data)
	{
		$request = \H2O::getContainer('request');
		Stdout::title('Debugger Info '.date('Y-m-d H:i:s'));
		Stdout::table([
			['Route','RunTime','Memory'],
			[$request->getRequestUri(),$data['runtime'],$data['memory']]
		]);
		$logfile = APP_RUNTIME.DS.'web'.DS.'debugger'.DS.date('Ymd').'.log'; //异常日志文件
		$content = Stdout::get(); //内容
		H2O\helpers\File::write($logfile,$content);//写入日志信息
	}
}