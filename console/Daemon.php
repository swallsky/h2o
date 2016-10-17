<?php
/**
 * 监控守护程序
 * @category   H2O
 * @package    console
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O,H2O\helpers\File;
class Daemon
{
    /**
     * @var string 日志路径
     */
    private $_logpath = '';
    /**
     * PHP Bin目录
     */
    private $_phpBin = '/usr/bin/php';
    /**
     * 子进程命令行前缀
     */
    private $_daemonson = '';
    /**
     * 构造函数 初始化
     */
    public function __construct()
    {
        $this->_logpath = APP_RUNTIME.DS.'console'.DS; //日志目录
        $this->_phpBin();
        $this->_daemonson = $this->_phpBin.' '.$GLOBALS['argv'][0].' ';
    }
    /**
     * 读取当前运行环境的php执行目录
     */
    private function _phpBin()
    {
        $handle = popen('which php', 'r');
        $bin = fread($handle, 2096);
        $this->_phpBin = empty($bin)?$this->_phpBin:$bin;
        pclose($handle);
    }
    /**
     * 主进程监控部分
     */
    public function actRun()
    {
        // 监控自身进程同时只允许运行一个实例
        $this->_checkSelfProc();
        //启动记录
        File::write(
            $this->_logpath . 'daemon' . DS . date('Ymd') . '.log',//记录日志信息 按天记录
            'time:'.date('Y-m-d H:i:s').',pid'.intval(getmypid()).PHP_EOL //写入信息
        );//写入日志信息
        while(true){
            //根据队列配置启动队列
            $this->_startProc();
            sleep(1);
        }
    }
    /**
     * 根据队列配置启动队列 只支持单个子进程任务
     */
    private function _startProc()
    {
        $adaemons = \H2O::getAppConfigs('daemon');
        $tasks = $this->_getTasks();
        foreach($adaemons as $que)
        {
            $_logFile = $this->_logpath . 'daemon' . DS . $que['route'] . DS . date('Ymd') . '.log'; //根据路由规则按天记录
            for($_taskno=1;$_taskno<=$que['daemonum'];$_taskno++)
            {
                if(!in_array($que['route'],$tasks)){//每个任务只充许一个进程在执行
                    $_cmd = "{$this->_daemonson} @service.daemon --c={$que['route']} >>{$_logFile} 2>&1 &";
                    $_pp = popen($_cmd, 'r');
                    pclose($_pp);
                }
            }
        }
    }
    /**
     * @return array 返回所有正在执行的任务列表
     */
    private function _getTasks()
    {
        $_cmd = "ps -ef | grep -v 'grep' | grep '{$GLOBALS['argv'][0]}' | awk '{print $11}'\n"; //$GLOBALS['argv'][0]为命令行的执行的php文件名
        $_pp = @popen($_cmd, 'r');
        $res = []; //返回所有正在执行的任务名
        if($_pp){//查看命令行是否有结果
            while(!feof($_pp)) {
                $_line = trim(fgets($_pp));
                $_line = substr($_line,4); //过滤--c=参数
                if(empty($_line)) continue;
                $res[] = $_line;
            }
        }
        @pclose($_pp);
        return $res;
    }
    /**
     * 检测自身进程，同时只允许运行一个实例
     * @return	NULL
     */
    private function _checkSelfProc()
    {
        $_cmd = "ps -ef | grep -v 'grep' | grep '{$GLOBALS['argv'][0]}' | awk '{print $10}'\n"; //$GLOBALS['argv'][0]为命令行的执行的php文件名
        $_procTotal = 0;
        $_pp = @popen($_cmd, 'r');
        if($_pp){//查看命令行是否有结果
            while(!feof($_pp)) {
                $_line = trim(fgets($_pp));
                if(empty($_line)) continue;
                if($_line != $GLOBALS['argv'][1]) continue;//匹配是不是主进程脚本
                $_procTotal++;
            }
        }
        @pclose($_pp);
        if($_procTotal>1) exit();
        return;
    }
}
?>