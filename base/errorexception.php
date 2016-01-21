<?php
/**
 * 自定义错误异常处理的类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
class ErrorException extends \ErrorException
{
	/**
	 * 初始化
	 * @param mixed $e
	 */
	public function __construct($e)
	{
		if(is_object($e)){
			$this->parseTrace($e);
		}
		exit();
	}
	/**
	 * 错误解析
	 * @param object $e
	 */
	private function parseTrace($e)
	{
		$trace = $e->getTrace();
		$user = []; $sys = [];
		foreach($trace as $t){
			if(!empty($t['file'])){
				$file = $t['file']; unset($t['file']);
				if(strpos($file,H2O_PATH) !== false){
					$sys[$file][] = $t['line'];
				}else{
					$user[$file][] = $t['line'];
				}
			}
		}
		$this->logTraceWeb($e->message, $user, $sys);
	}
	/**
	 * 错误显示格式 以web形式直接显示
	 * @param string $message 错误信息
	 * @param array $project 项目代码定位信息
	 * @param array $framework 框架定位信息
	 */
	private function logTraceWeb($message,$project,$framework)
	{
		header("Content-type: text/html; charset=utf-8");
		echo '<div><b>应用目录:</b>'.APP_PATH.'</div>';
		echo '<div><b>错误信息:</b><span style="color:red;">'.$message.'</span></div>';
		echo '<div><b>关联文件列表:</b></div>';
		$this->logLineWeb($project);
		$this->logLineWeb($framework);
	}
	/**
	 * 根据行数查找上下文
	 * @param array $fre
	 * @param int $lspt 上下文行数
	 */
	private function logLineWeb($fre,$lspt = 2)
	{
		foreach($fre as $f=>$lines){
			$sfile = file($f); //读取文件信息
			$trow = count($sfile); //该文件总行数
			echo '<div><b>文件:</b><span style="color:red;">'.$f.'</span><b>第'.join('、',$lines).'行</b></div>';
			foreach($lines as $line){
				$min = $line-$lspt; $max = $line+$lspt;
				$min = $min<0?0:$min;
				$max = $max>$trow?$trow:$max;
				echo '<div>---------------------</div>';
				for($l=$min;$l<=$max;$l++){
					echo '<div '.($l==$line?'style="color:red;"':'').'>'.$l.':'.$sfile[$l-1].'</div>';
				}
			}
		}
	}
}
?>