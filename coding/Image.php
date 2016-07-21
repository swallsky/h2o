<?php
/**
 * 实例应用代码镜像
 * @category   H2O
 * @package    coding
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\coding;
use H2O\helpers\File;
class Image
{
    /**
     * @var string 模板目录
     */
    private $_tpldir;
    /**
     * 初始化
     */
    public function __construct()
    {
        $this->_tpldir = __DIR__.DS; //当前代码模板目录
    }
    /**
     * @return array 开发者信息
     */
    private function _devauthor()
    {
        $config = \H2O::getAppConfigs('devauthor'); //读取开发者配置信息
        return [
            'T_PROGRAM'   =>  isset($config['program'])?$config['program']:'', //项目信息
            'T_AUTHOR'    =>  isset($config['author'])?$config['author']:'', //开发者信息
            'T_DEVTIME'   =>  date('Y-m-d H:i:s') //开发时间
        ];
    }
    /**
     * 复制单个镜相文件
     * @param $resource 原文件
     * @param $dest 目标文件
     * @param $data 需要替换的内容
     */
    public function file($resource,$dest,$data = [])
    {
        $code = file_get_contents($this->_tpldir.$resource);
        $othur = $this->_devauthor();
        foreach($othur as $k=>$v){
            $code = str_replace($k,$v,$code); //替换开发者变量信息
        }
        $code = str_replace($data['search'],$data['replace'],$code); //替换变量信息
        return file::write($dest,$code);
    }
}