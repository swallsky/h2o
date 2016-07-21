<?php
/**
 * console的控制器
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\helpers\File;
class Gii
{
    /**
     * @var 应用根目录
     */
    private $_apppath;
    /**
     * @var 应用目录
     */
    private $_appdir;
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->_apppath = \H2O::getAppRootPath();
    }
    /**
     * 读取所有应用列表信息
     * @return array|void
     */
    private function _readApps()
    {
        if (!($handle = opendir($this->_apppath))){
            return;
        }
        while (($dir = readdir($handle)) !== false) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $path = $this->_apppath . DS . $dir;
            if (is_dir($path)) {
                $this->_appdir[$dir] = $path;
            }
        }
        closedir($handle);
        return $this->_appdir;
    }
    /**
     * 按限定的范围输入
     * @param $rd 输入值限定范围，例如[1,2,3,4]、['d1','d2','d3']，如果为空，则不限定,输入错误三次，程序退出
     * @return string 返回输入的值
     */
    private function _getInputRangVal($rd = [])
    {
        if(empty($rd)){
            return trim(fgets(STDIN));
        }else{
            $res = trim(fgets(STDIN));
            if(!in_array($res,$rd)){
                echo 'Input error,please input again:';
                $res = trim(fgets(STDIN));
            }
            if(!in_array($res,$rd)){
                echo 'Input error,please input again:';
                $res = trim(fgets(STDIN));
            }
            if(!in_array($res,$rd)){
                exit('Enter the wrong number of times too much'.PHP_EOL);
            }
            return $res;
        }
    }
    /**
     * 正确的命名
     * @return string 正确的变量名/类名/方法名
     */
    private function _getInputCodeVar()
    {
        $pattern = "/^([a-zA-Z]+)([a-zA-Z0-9])$/";
        $res = trim(fgets(STDIN));
        if(!preg_match($pattern,$res)){
            echo 'Input error,please input again:';
            $res = trim(fgets(STDIN));
        }
        if(!preg_match($pattern,$res)){
            echo 'Input error,please input again:';
            $res = trim(fgets(STDIN));
        }
        if(!preg_match($pattern,$res)){
            exit('Enter the wrong number of times too much'.PHP_EOL);
        }
        return $res;
    }
    /**
     * 输入正确的类名
     * @param $type 分类信息 controllers/models
     * @param $app 应用目录名
     * @return string 正确的类名
     */
    private function _getClassName($type,$app)
    {
        $class = $this->_getInputCodeVar();
        $class = ucfirst($class);
        $cfile = $this->_appdir[$app].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $this->_appdir[$app].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $this->_appdir[$app].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            exit('Enter the wrong number of times too much'.PHP_EOL);
        }
        return $class;
    }
    /**
     * 复制镜像
     * @param $type 分类信息 controllers/models
     * @param $resource 源镜像文件
     * @param $app 应用目录名
     * @param $class 类名
     * @param $conf 镜像配置信息
     */
    private function _copyImage($type,$resource,$app,$class,$conf = [])
    {
        $oimg = new \H2O\coding\Image();
        $cfile = $this->_appdir[$app].DS.$type.DS.$class.'.php';
        if(empty($conf)) $conf = ['search'=>[],'replace'=>[]];
        $conf['search'][] = 'T_NAMESPACE';//命名空间
        $conf['search'][] = 'T_CLASS';//类名
        $conf['replace'][] = ltrim(\H2O::APP_ROOT_NAME.'\\'.$app.'\\'.$type,'\\');//类名
        $conf['replace'][] = $class;//类名
        return $oimg->file($resource,$cfile,$conf);
    }
    /**
     * 创建Web应用
     */
    public function actWeb()
    {
        $apps = $this->_readApps();
        echo 'Please choose your web app:'.PHP_EOL;
        $i=1;$app = [];$input = [];
        foreach($apps as $k=>$p){
            echo $i.':'.$k.PHP_EOL;
            $app[$i] = $k;
            $input[] = $i;
            $i++;
        }
        echo "Enter your number：";
        $apn = $this->_getInputRangVal($input);
        echo 'Please input your web class name:';
        $class = $this->_getClassName('controllers',$app[$apn]);
        $this->_copyImage('controllers','app/empty/web.php',$app[$apn],$class);
        echo 'Web app create success!'.PHP_EOL;
    }
    /**
     * 创建Cli应用
     */
    public function actCli()
    {
        $apps = $this->_readApps();
        echo 'Please choose your cli app:'.PHP_EOL;
        $i=1;$app = [];$input = [];
        foreach($apps as $k=>$p){
            echo $i.':'.$k.PHP_EOL;
            $app[$i] = $k;
            $input[] = $i;
            $i++;
        }
        echo "Enter your number：";
        $apn = $this->_getInputRangVal($input);
        echo 'Please input your cli class name:';
        $class = $this->_getClassName('controllers',$app[$apn]);
        $this->_copyImage('controllers','app/empty/cli.php',$app[$apn],$class);
        echo 'Cli app create success!'.PHP_EOL;
    }
    /**
     * 创建模型
     */
    public function actModel()
    {
        $apps = $this->_readApps();
        echo 'Please choose your model app:'.PHP_EOL;
        $i=1;$app = [];$input = [];
        foreach($apps as $k=>$p){
            echo $i.':'.$k.PHP_EOL;
            $app[$i] = $k;
            $input[] = $i;
            $i++;
        }
        echo "Enter your number：";
        $apn = $this->_getInputRangVal($input);
        echo 'Please input your model class name:';
        $class = $this->_getClassName('models',$app[$apn]);
        $this->_copyImage('models','app/empty/dbc.php',$app[$apn],$class);
        echo 'Web app create success!'.PHP_EOL;
    }
}