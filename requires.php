<?php
/**
 * 依耐环境检查
 * @category   H2O
 * @package    core
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
/**
 * 路径分隔符
 */
define('DS',DIRECTORY_SEPARATOR);
/**
 * 框架根目录
 */
defined('H2O_PATH') or define('H2O_PATH', __DIR__);
/**
 * composer安装目录
 */
defined('VENDOR_PATH') or define('VENDOR_PATH',dirname(dirname(H2O_PATH)));
/**
 * 系统的根目录
 */
defined('APP_PATH') or define('APP_PATH', dirname(dirname(dirname(__DIR__))));
/**
 * 运行时缓存目录
 */
defined('APP_RUNTIME') or define('APP_RUNTIME', APP_PATH.DS.'runtime');

class Requires
{
    /**
     * @var array 错误信息
     */
    private $_errorMsg = array();
    /**
     * @var array 定义的可读写的目录
     */
    private $_defWriteDir = array();
    /**
     * @var string 缓存环境标识
     */
    private $_cacheTag = 'cache_env';
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->_defWriteDir[] = APP_RUNTIME;
    }
    /**
     * php 版本验证
     * @return bool
     */
    private function _version()
    {
        if (version_compare(PHP_VERSION, '5.4.0') < 0){
            $this->_errorMsg['php_version'] = 'Minimum php version 5.4.0, current php version '.PHP_VERSION;
        }
    }
    /**
     * php扩展检查
     * @return bool
     */
    private function _extensions()
    {
        $exends = get_loaded_extensions();
        if(!in_array('PDO',$exends)){//pdo扩展验证
            $this->_errorMsg['pdo_extensions'] = 'PDO extensions not found';
        }
        if(!in_array('pdo_mysql',$exends)){//pdo扩展验证
            $this->_errorMsg['pdo_mysql'] = 'PDO pdo_mysql extensions not found';
        }
    }
    /**
     * 添加可读写目录
     * @param mixed $dir
     */
    public function addIsWrite($dir)
    {
        if(is_string($dir)){
            $this->_defWriteDir[] = APP_PATH.DS.trim($dir,DS).DS;
        }
        if(is_array($dir)){
            foreach($dir as $d){
                $this->_defWriteDir[] = APP_PATH.DS.trim($d,DS).DS;
            }
        }
    }
    /**
     * 验证读写权限
     * @return bool
     */
    private function _iswrite()
    {
        foreach($this->_defWriteDir as $d){
            if(!is_writable($d)){
                $this->_errorMsg[$d] = 'no read and write permissions';
            }
        }
    }
    /**
     * @param $tag 添加缓存标识
     */
    public function addCacheTag($tag)
    {
        $this->_cacheTag = $tag;
    }
    /**
     * @return string 获取存储路径
     */
    private function _getFilePath()
    {
        //加入PHP版本信息,更换php版本时,则重新较验
        return APP_RUNTIME.DS.'requires_'.md5($this->_cacheTag.PHP_VERSION);
    }
    /**
     * 缓存信息验证,防止重复检测运行环境,默认情况是需要重新检测环境
     * @return bool
     */
    private function _cacheVerify()
    {
        $tag = $this->_getFilePath();
        if(file_exists($tag)){
            $filet = filemtime($tag);//读取缓存时间
            return $filet == file_get_contents($tag);
        }
        return false;
    }
    /**
     * 验证结果
     * @return bool
     */
    public function verify()
    {
        if(!$this->_cacheVerify()){//是否需要重新检测环境
            //版本验证
            $this->_version();
            //扩展验证
            $this->_extensions();
            //读写验证
            $this->_iswrite();
            if(empty($this->_errorMsg)){//验证成功
                $tag = $this->_getFilePath();
                file_put_contents($tag,time()); //写入缓存信息
                return true;
            }else{//否则显示运行环境错误
                return false;
            }
        }else{//环境通过
            return true;
        }
    }
    /**
     * 返回错误信息
     * @return array
     */
    public function getErrorMsg()
    {
        return $this->_errorMsg;
    }
}
