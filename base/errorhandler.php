<?php
/**
 * 错误和异常捕获程序基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
class ErrorHandler
{
    /**
     * @var Exception 当前异常信息
     */
    public $exception;
    /**
     * 注册错误句柄
     */
    public function register()
    {
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
        //输出错误日志信息
        $this->logException($exception);
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
            echo $code;
        }
    }

    /**
     * 致命错误句柄
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        if ($this->isFatalError($error)) {
            print_r($error);
        }
    }
    /**
     * 异常日志
     * @param Exception $exception 异常信息
     */
    public function logException($exception)
    {
        var_dump($exception);
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