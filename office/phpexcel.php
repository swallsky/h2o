<?php
/**
 * 基于phpExcel的excel导入、导出
 * @category   H2O
 * @package    phpoffice
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\office;
//phpexcel的包目录
define('PHPEXCEL_DIR',VENDOR_PATH.DS.'phpoffice'.DS.'phpexcel'.DS);
//phpexcel cache 目录
define('PHPEXCEL_CACHE_DIR',APP_RUNTIME.DS.'phpexcel');
/** Include PHPExcel_IOFactory */
require_once(PHPEXCEL_DIR .'Classes'.DS.'PHPExcel'.DS.'IOFactory.php');

use H2O\helpers\File;

/**
 * Example 1:
 * 小数据量导入处理
    ~~~
    $file = '/d/s/test.xlsx';
    $obj = new \H2O\office\Phpexcel();
    $obj->Import($file,0);
    $error = $obj->VerHeader([
        'A' =>  '姓名',
        'B' =>  '性别',
        'C' =>  '地址',
        'D' =>  '职业',
        'E' =>  '入职日期'
    ]);
    if(!empty($error)){
        //格式错误信息提示
        print_r($error);
    }
    $data = $obj->GetSimpleData([
        'A' =>  ['name'],
        'B' =>  ['sex'],
        'C' =>  ['address'],
        'D' =>  ['job'],
        'E' =>  ['rdate','time','Y-m-d H:i:s'] //第一个参数为字段名 第二个为字段类型默认为string
    ]);
    //处理数据
    ~~~
 * Example 2:
 * 大数据的Excel导入切片处理办法
    ~~~
    $file = '/d/s/test.xlsx';
    $obj = new \H2O\office\Phpexcel();
    $obj->Import($file,0);
    $error = $obj->VerHeader([
        'A' =>  '姓名',
        'B' =>  '性别',
        'C' =>  '地址',
        'D' =>  '职业',
        'E' =>  '入职日期'
    ]);
    if(!empty($error)){
        //格式错误信息提示
        print_r($error);
    }
    $queue = $obj->QueueData([
        'A' =>  ['name'],
        'B' =>  ['sex'],
        'C' =>  ['address'],
        'D' =>  ['job'],
        'E' =>  ['rdate','time','Y-m-d H:i:s'] //第一个参数为字段名 第二个为字段类型默认为string
    ],10000);
    //循环读取切片信息
    foreach($queue as $q){
        $simpledata = $obj->GetSwpData($q);
        //处理切片数据
    }
    ~~~
 */
class Phpexcel
{
    /**
     * @var string 文件名
     */
    private $_file = '';
    /**
     * Object Excel对象
     */
    private $_oexcel = null;
    /**
     * Int 行数
     */
    private $_rows = 0;
    /**
     * Int 列数
     */
    private $_cols = 0;
    /**
     * Object 当前sheet对象
     */
    private $_sheet = null;
    /**
     * 导入初始化
     * @param $file 导入的excel文件路径
     * @param $sheetnum 读取工作表顺序
     */
    public function Import($file,$sheetnum = 0)
    {
        $this->_file = $file; //文件名
        $this->_oexcel = \PHPExcel_IOFactory::load($file); //载入文档
        $this->_sheet = $this->_oexcel->getSheet($sheetnum); // 读取第一個工作表
        $this->_rows = $this->_sheet->getHighestRow(); // 取得总行数
        $this->_cols = $this->_sheet->getHighestColumn(); // 取得总列数
    }
    /**
     * 验证表头信息是否正确
     * @param array $header
     * @return array 如果为空，则表示表头正确，如果不为空，则返回错误列的数组
     */
    public function VerHeader($header = [])
    {
        $error = [];
        for ($column = 'A'; $column <= $this->_cols; $column++) {//列数是以A列开始
            $dd =$this->_sheet->getCell($column.'1')->getValue();
            if($dd != $header[$column]){
                $error[$column] = $header[$column];
            }
        }
        return $error;
    }
    /**
     * 解析单行数据
     * @param $field 需要格式化的字段，例如
     * [
     *      'A' =>  ['name'],
     *      'B' =>  ['rdate','time','Y-m-d H:i:s']
     * ]
     * @param $row 行号
     * @return array 单行数据
     */
    private function _paserColsData($field,$row)
    {
        $tmp = [];
        for ($column = 'A'; $column <= $this->_cols; $column++) {//列数是以A列开始
            if(!isset($field[$column])){
                throw new \Exception('Field `'.$column.'` is undefined!');
            }else{
                $key = $field[$column][0];
                $type = isset($field[$column][1])?$field[$column][1]:'string';
            }
            $val = $this->_sheet->getCell($column.$row)->getValue();
            if($type=='time'){
                if(isset($field[$column][2])){
                    $tmp[$key] = date($field[$column][2],\PHPExcel_Shared_Date::ExcelToPHP($val));
                }else{
                    throw new \Exception('Field `'.$column.'` date format is undefined!');
                }
            }else{
                $tmp[$key] = $val;
            }
        }
        return $tmp;
    }
    /**
     * 返回excel数据 少量数据时，直接调用数据
     * @param array $field 需要格式化的字段，例如
     * [
     *      'A' =>  ['name'],
     *      'B' =>  ['rdate','time','Y-m-d H:i:s']
     * ]
     * @return array
     */
    public function GetSimpleData($field)
    {
        $data = [];
        for ($row = 2; $row <= $this->_rows; $row++){//行数是以第1行开始
            $data[] = $this->_paserColsData($field,$row); //解析单行数据
        }
        return $data;
    }
    /**
     * 数据切割队列
     * @param array $field 需要格式化的字段，例如 ['A'=>['name'],'B'=>['rdate'=>'time']]
     * @param int $cn 每个切割文件行数 默认为1万条为一个
     * @return array 切分的文件队列信息
     */
    public function QueueData($field,$cn = 10000)
    {
        $data = [];
        $dirs = PHPEXCEL_CACHE_DIR . DS . md5($this->_file); //文件块目录
        $filei = 1; //文件切分数
        $filequeue = []; //清空之前的队列信息
        for ($row = 2; $row <= $this->_rows; $row++){//行数是以第1行开始
            $data[] = $this->_paserColsData($field,$row);
            $line = $row-2;
            if($line % $cn == 0 && $line>0){
                $file = $dirs.DS.$filei.'.swp.php';
                $filequeue[] = $file;//写入到文件队列中
                $content = "<?php\n return ".var_export($data,TRUE).";\n?>";
                File::write($file,$content,false); //写入缓存信息
                $data = []; //清空之前的数据
                $filei++;
            }
        }
        //处理剩余的
        if(!empt($data)){
            $file = $dirs.DS.$filei.'.swp.php';
            $filequeue[] = $file;//写入到文件队列中
            $content = "<?php\n return ".var_export($data,TRUE).";\n?>";
            File::write($file,$content,false); //写入缓存信息
        }
        return $filequeue;
    }
    /**
     * 获取切片数据
     * @param $file 切片单文件
     * @return array 返回切片数据
     */
    public function GetSwpData($file)
    {
        if(file_exists($file)){
            return include($file);
        }else{
            return [];
        }
    }
}