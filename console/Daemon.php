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
        $this->_daemonson = $this->_phpBin.' '.realpath($GLOBALS['argv'][0]).' '; //返回标准备的命令行
    }
    /**
     * 读取当前运行环境的php执行目录
     */
    private function _phpBin()
    {
        $handle = popen('which php', 'r');
        $bin = fread($handle, 2096);
        $this->_phpBin = empty($bin)?$this->_phpBin:trim($bin);
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
     * 查看当前任务列表
     */
    public function actGetTask()
    {
        $tasks = $this->_getTasks();
        foreach($tasks as $v){
            echo $v.PHP_EOL;
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
            $tmpdir = $this->_logpath.'daemon'.DS.$que['route']; //当前任务日志目录
            file::createDirectory($tmpdir); //如果目录不存在,则创建
            $_logFile = $tmpdir.DS.date('Ymd').'.log'; //根据路由规则按天记录
            if(!in_array($que['route'],$tasks)){//每个任务只充许一个进程在执行
                $_cmd = "{$this->_daemonson} @service.daemon --c={$que['route']} >>{$_logFile} 2>&1 &".PHP_EOL;
                $_pp = popen($_cmd, 'r');
                pclose($_pp);
            }
        }
    }
    /**
     * @return array 返回所有正在执行的任务列表
     */
    private function _getTasks()
    {
        $_cmd = "ps -ef | grep -v 'grep' | grep '{$this->_daemonson} @service.daemon' | awk '{print $13}'\n";
        $_pp = popen($_cmd, 'r');
        $res = []; //返回所有正在执行的任务名
        if($_pp){//查看命令行是否有结果
            while(!feof($_pp)) {
                $_line = trim(fgets($_pp));
                $_line = substr($_line,4); //过滤--c=参数
                if(empty($_line)) continue;
                $res[] = $_line;
            }
        }
        pclose($_pp);
        return $res;
    }
    /**
     * 检测自身进程，同时只允许运行一个主进程
     * stat 中的参数意义如下：
    D 不可中断 Uninterruptible（usually IO）
    R 正在运行，或在队列中的进程
    S 处于休眠状态
    T 停止或被追踪
    Z 僵尸进程
    W 进入内存交换（从内核2.6开始无效）
    X   死掉的进程

    < 高优先级
    n   低优先级
    s   包含子进程
    +   位于后台的进程组
     * @return	NULL
     */
    private function _checkSelfProc()
    {
        $_cmd = "ps aux | grep -v 'grep' | grep '{$GLOBALS['argv'][0]} {$GLOBALS['argv'][1]}' | awk '{print $2,$8}'\n"; //查找守护进程执行状态和进程ID
        $_pp = popen($_cmd, 'r');
        $_akill = [];//需要清理的进程
        $_ptotal = 0; //启动的进程数
        if($_pp){//查看命令行是否有结果
            while(!feof($_pp)) {
                $_line = trim(fgets($_pp));
                if(empty($_line)) continue;
                list($_pid, $_status) = explode(' ', $_line);
                if($_status != 'S+'){//不是正在执行的状态,将记录kill
                    $_akill[] = $_pid;
                }else{
                    $_ptotal++; //当前运行进程加1
                }
            }
        }
        if(!empty($_akill)){
            foreach($_akill as $_kid){ //清理异常进程
                popen("kill -9 ".$_kid,'r');
            }
        }
        pclose($_pp);
        if($_ptotal>1) exit(); //当前已有守护进程存在,不需要再启动
        return;
    }
}
?>