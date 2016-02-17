<?php
/**
 * 错误和异常捕获程序基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
class ErrorHandler
{
   	/**
     * @var boolean 是否不显示错误提示。默认值为false
     */
    public $discardExistingOutput = false;
    /**
     * 注册错误句柄
     */
    public function register()
    {
    	error_reporting(0);
        ini_set('display_errors', false);//关闭系统错误提示
        set_exception_handler([$this, 'handleException']); //用户自定义异常处理方法
        set_error_handler([$this, 'handleError']);//用户自定义错误处理方法
        register_shutdown_function([$this, 'handleFatalError']); //自定义致命错误方法
    }

    /**
     * 恢复错误处理
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * 自定义异常处理方法
     * @param Exception $exception 未捕获的异常
     */
    public function handleException($exception)
    {
        //防止处理错误或异常出现递归错误
        $this->unregister();
        $this->logException($exception);
        exit(1);
    }

    /**
     * 用户自定义错误处理方法
     * @param integer $code 错误代码
     * @param string $message 错误信息
     * @param string $file 错误的文件
     * @param integer $line 错误的行号
     */
    public function handleError($code, $message, $file, $line)
    {
       if (error_reporting() & $code) {
            $exception = new \ErrorException($message, $code, $code, $file, $line);
            //代码跟踪信息
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            foreach ($trace as $frame) {
                if ($frame['function'] == '__toString') {
                    $this->handleException($exception);
                    exit(1);
                }
            }
            throw $exception;
        }
        return false;
    }

    /**
     * 致命错误句柄
     */
    public function handleFatalError()
    {
        $error = error_get_last();
    	if ($this->isFatalError($error)) {
    		$exception = new \ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->logException($exception);
            exit(1);
        }
    }
    /**
     * 异常日志
     * @param Exception $exception 异常信息
     */
    public function logException($exception)
    {
    	$data = $this->parseException($exception);
    	$logger = \H2O::getContainer('logger');
    	$env = \H2O::getRunEnv(); //获取运行环境
    	if($env=='prod'){//生产环境
    		$logger->exceptionWrite($data['message'],$data['files']); //写入错误日志
    	}else{//开发、测试环境 都直接显示错误
    		$logger->exceptionDebug($data['message'],$data['files']); //显示错误日志 方便调试
    	}
        if($this->discardExistingOutput){
        	$this->clearOutput();
        }
    }
    /**
     * 解析异常信息
     * @param object $e
     * @return array
     */
    public function parseException($e)
    {
    	$trace = $e->getTrace();
    	$files = [];$pro=[];$sys = [];
    	$gfile = $e->getFile();
    	if(!empty($gfile)){
    		$files[] = ['file'=>$gfile,'line'=>$e->getLine()];
    	}
    	foreach($trace as $t){
    		if(!empty($t['file'])){
    			$files[] = ['file'=>$t['file'],'line'=>$t['line']];
    		}
    	}
    	foreach ($files as $t){
    		if(!empty($t['file'])){
    			$file = $t['file']; unset($t['file']);
    			if(strpos($file,H2O_PATH) !== false){
    				$sys[$file][] = $t['line'];
    			}else{
    				$pro[$file][] = $t['line'];
    			}
    		}
    	}
    	$files = array_merge($pro,$sys);
    	return ['message'=>$e->getMessage(),'files'=>$files];
    }
    /**
     * 在调用这个方法之前删除所有输出响应。
     */
    public function clearOutput()
    {
    	for ($level = ob_get_level(); $level > 0; --$level) {
    		if (!@ob_end_clean()) {
    			ob_clean();
    		}
    	}
    }
    /**
     * 致命错误类型
     *
     * @param array $error error_get_last()
     * @return boolean 是否是一个致命错误类型
     */
    private function isFatalError($error)
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
    }
}