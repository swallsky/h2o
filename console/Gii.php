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
     * 构造函数
     */
    public function __construct()
    {
        $this->_apppath = \H2O::getAppRootPath();
    }
    /**
     * 读取当前目录下的一级目录
     * @return array|void
     */
    private function _readDir($cdir)
    {
        if (!($handle = opendir($cdir))){
            return;
        }
        $names = [];
        while (($dir = readdir($handle)) !== false) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $path = $cdir.DS.$dir;
            if (is_dir($path)) {
                $names[$dir] = $path;
            }
        }
        closedir($handle);
        return $names;
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
     * @param $app 应用目录信息 name:应用名,path:应用路径
     * @return string 正确的类名
     */
    private function _getClassName($type,$app)
    {
        $class = $this->_getInputCodeVar();
        $class = ucfirst($class);
        $cfile = $app['path'].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $app['path'].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $app['path'].DS.$type.DS.$class.'.php';
        if(file_exists($cfile)){
            exit('Enter the wrong number of times too much'.PHP_EOL);
        }
        return $class;
    }
    /**
     * 复制镜像
     * @param $type 分类信息 controllers/models
     * @param $resource 源镜像文件
     * @param $app 应用目录信息 name:应用名,path:应用路径
     * @param $class 类名
     * @param $conf 镜像配置信息
     */
    private function _copyImage($type,$resource,$app,$class,$conf = [])
    {
        $oimg = new \H2O\coding\Image();
        $cfile = $app['path'].DS.$type.DS.$class.'.php';
        if(empty($conf)) $conf = ['search'=>[],'replace'=>[]];
        $conf['search'][] = 'T_NAMESPACE';//命名空间
        $conf['search'][] = 'T_CLASS';//类名
        $conf['search'][] = 'T_MODEL_NAMESPACE';//模型命名空间
        $conf['replace'][] = ltrim(\H2O::APP_ROOT_NAME.'\\'.$app['name'].'\\'.$type,'\\');//类名
        $conf['replace'][] = $class;//类名
        $conf['replace'][] = ltrim(\H2O::APP_ROOT_NAME.'\\'.$app['name'].'\\models','\\');//模型命名空间
        return $oimg->file($resource,$cfile,$conf);
    }
    /**
     * 返回选择的应用
     * @return array
     */
    private function _chooseApps()
    {
        $apps = $this->_readDir($this->_apppath);
        echo 'Please choose your application:'.PHP_EOL;
        $i=1;$app = [];$input = [];
        foreach($apps as $k=>$p){
            echo $i.':'.$k.PHP_EOL;
            $app[$i] = $k;
            $input[] = $i;
            $i++;
        }
        echo "Enter your number：";
        $apn = $this->_getInputRangVal($input);
        return ['name'=>$app[$apn],'path'=>$apps[$app[$apn]]];
    }
    /**
     * 创建Web应用
     */
    public function actWeb()
    {
        $app = $this->_chooseApps();
        echo 'Please input your web class name:';
        $class = $this->_getClassName('controllers',$app);
        $this->_copyImage('controllers','app/empty/web.php',$app,$class);
        echo 'Web app create success!'.PHP_EOL;
    }
    /**
     * 创建Cli应用
     */
    public function actCli()
    {
        $app = $this->_chooseApps();
        echo 'Please input your cli class name:';
        $class = $this->_getClassName('controllers',$app);
        $this->_copyImage('controllers','app/empty/cli.php',$app,$class);
        echo 'Cli app create success!'.PHP_EOL;
    }
    /**
     * 创建模型
     */
    public function actModel()
    {
        $app = $this->_chooseApps();
        echo 'Please choose type:'.PHP_EOL;
        echo '1:Command'.PHP_EOL;
        echo '2:TableStrategy'.PHP_EOL;
        echo "Enter your number：";
        $tp = $this->_getInputRangVal([1,2]);
        echo 'Please input your model class name:';
        $class = $this->_getClassName('models',$app);
        $type = [1=>'dbc',2=>'dbt'];
        $this->_copyImage('models','app/empty/'.$type[$tp].'.php',$app,$class);
        echo 'Model create success!'.PHP_EOL;
    }
    /**
     * 选择组合
     * @param $gp
     * @return array
     */
    private function _chooseGroup($gp)
    {
        $oimg = new \H2O\coding\Image();
        $simdir = $oimg->getTplDir().'app'.DS.$gp;
        $smch = $this->_readDir($simdir); //读取目录
        echo 'Please choose your application group:'.PHP_EOL;
        $i=1;$aptyp = [];$input = [];
        foreach($smch as $k=>$p){
            echo $i.':'.$k.PHP_EOL;
            $aptyp[$i] = $k;
            $input[] = $i;
            $i++;
        }
        echo "Enter your number：";
        $aptn = $this->_getInputRangVal($input);
        $group = $aptyp[$aptn]; //对应的应用组合
        $groupath = $smch[$group]; //对应组合目录
        return ['name'=>$group,'path'=>$groupath];
    }
    /**
     * 输入正确的组名
     * @param $app 应用目录信息 name:应用名,path:应用路径
     * @return string 正确的组名
     */
    private function _getGroupName($app)
    {
        $class = $this->_getInputCodeVar();
        $class = ucfirst($class);
        $cfile = $app['path'].DS.'controllers'.DS.$class.'.php'; //控制器
        $mfile = $app['path'].DS.'models'.DS.$class.'.php'; //模型
        if(file_exists($cfile)){//如果控制器存在，则重新输入
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        if(file_exists($mfile)){//如果模型已存在，则重新输入
            echo 'File exists:\''.$mfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $app['path'].DS.'controllers'.DS.$class.'.php'; //控制器
        $mfile = $app['path'].DS.'models'.DS.$class.'.php'; //模型
        if(file_exists($cfile)){//如果控制器存在，则重新输入
            echo 'File exists:\''.$cfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        if(file_exists($mfile)){//如果模型已存在，则重新输入
            echo 'File exists:\''.$mfile.'\''.PHP_EOL;
            echo 'please input again:';
            $class = $this->_getInputCodeVar();
            $class = ucfirst($class);
        }
        $cfile = $app['path'].DS.'controllers'.DS.$class.'.php'; //控制器
        $mfile = $app['path'].DS.'models'.DS.$class.'.php'; //模型
        if(file_exists($cfile) || file_exists($mfile)){
            exit('Enter the wrong number of times too much'.PHP_EOL);
        }
        return $class;
    }
    /**
     * 创建简单组合应用
     */
    public function actSimple()
    {
        $app = $this->_chooseApps();
        $group = $this->_chooseGroup('simple');
        echo 'Please input your living name:';
        $class = $this->_getGroupName($app);
        $this->_copyImage('controllers','app/simple/'.$group['name'].'/controller.php',$app,$class);
        $this->_copyImage('models','app/simple/'.$group['name'].'/model.php',$app,$class);
        echo 'Simple group application create success!'.PHP_EOL;
    }
}