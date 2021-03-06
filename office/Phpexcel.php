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
     * array 表头信息
     */
    private $_headers = [];
    /**
     * Object 当前sheet对象
     */
    private $_sheet = null;
    /**
     * @var Object excel初始化对象
     */
    private $_excelObj = null;
    /**
     * @var Object 当前激活状态的sheet
     */
    private $_activeSheet = null;
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
    }
    /**
     * 验证表头信息是否正确
     * @param array $header
     * @return array 如果为空，则表示表头正确，如果不为空，则返回错误列的数组
     */
    public function VerHeader($header)
    {
        $error = [];
        foreach($header as $k=>$v){
            $dd =$this->_sheet->getCell($k.'1')->getValue();
            if($dd != $v){
                $error[$k] = $v;
            }
        }
        $this->_headers = $header;
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
        foreach($this->_headers as $k=>$v){
            if(!isset($field[$k])){
                throw new \Exception('Field `'.$k.'` is undefined!');
            }else{
                if(is_array($field[$k])){//兼容混合模式
                    $key = $field[$k][0];
                    $type = isset($field[$k][1])?$field[$k][1]:'string';
                }else{
                    $key = $field[$k];
                    $type = 'string';
                }
            }
            $cell = $this->_sheet->getCell($k.$row);
            $val = $cell->getValue();
            $val = ($val instanceof \PHPExcel_RichText)?$val->getPlainText():$val; //如果是富文本,则转换为普通文本信息

            if($type=='time'){//时间格式
                if(isset($field[$k][2])){
                    if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){//如果为数字类型的时间字段，则进行转换
                        $tmp[$key] = gmdate($field[$k][2],\PHPExcel_Shared_Date::ExcelToPHP($val));
                    }else{
                        $tmp[$key] = date($field[$k][2],strtotime($val));
                    }
                }else{
                    throw new \Exception('Field `'.$k.'` date format is undefined!');
                }
            }else{
                $tmp[$key] = $val;
            }
        }
        $isempty = implode('',$tmp); //合并单行内容，过滤空行
        return empty($isempty)?false:$tmp;
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
            $res = $this->_paserColsData($field,$row); //解析单行数据
            if($res!==false){
                $data[] = $res;
            }
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
        if(!empty($data)){
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
    /**
     * 初始化phpexcel对象
     */
    public function export()
    {
        $this->_excelObj = new \PHPExcel();
    }
    /**
     * 设置excel属性
     * @param array $attr
     * @return Object
     */
    public function setAttribute($attr = [])
    {
        $default = [
            'Creator'           =>  '', //创建者
            'LastModifiedBy'    =>  '', //修改者
            'Title'             =>  '', //标题
            'Subject'           =>  '', //主题
            'Description'       =>  '', //描述
            'Keywords'          =>  '', //关键字
            'Category'          =>  '', //类型
        ];
        $proper = $this->_excelObj->getProperties();
        foreach($default as $k=>$v){
            $v = isset($attr[$k])?$attr[$k]:$v; //如果不存在，则使用默认信息
            $proper->{'set'.$k}($v);
        }
        return $this->_excelObj;
    }
    /**
     * 设置单元格样式
     * @param $key 列标识 例如 A1
     * @param $val 需要设置的值
     * @param array $format
     */
    private function _setCellValue($key,$val,$format = [])
    {
        $style = $this->_activeSheet->getStyle($key);
        $deformat = [//通用样式
            'name'      =>  '微软雅黑',//字体
            'width'     =>  'auto', //设置宽度 ,默认为自动宽度
            'bold'      =>  0, //是否加粗，默认为否
            'size'      =>  12
        ];
        $deformat = array_merge($deformat,$format);
        $font = $style->getFont();
        foreach($deformat as $k=>$v){
            switch($k){
                case 'name':
                    $this->_activeSheet->setCellValue($key,$val); //设置值
                    $font->setName($v);
                    break;
                case 'width'://宽度
                    $this->_activeSheet->setCellValue($key,$val); //设置值
                    if($v == 'auto'){//自动宽度
                        $this->_activeSheet->getColumnDimension($key)->setAutoSize(true);
                    }else{//固定宽度
                        $this->_activeSheet->getColumnDimension($key)->setWidth($v);
                    }
                    break;
                case 'bold'://加粗
                    $this->_activeSheet->setCellValue($key,$val); //设置值
                    $font->setBold($v);
                    break;
                case 'size': //字段大小
                    $this->_activeSheet->setCellValue($key,$val); //设置值
                    $font->setSize($v);
                    break;
                case 'datetime': //设置时间格式
                    $this->_activeSheet->setCellValue($key,date($v,strtotime($val))); //设置值
                    $this->_activeSheet->getStyle($key)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                    break;
            }
        }
    }
    /**
     * 设置表头
     * @param array $header 表头信息 如果为字符，则直接使用默认格式
     * @param int $sheet
     */
    public function setHeader($header = [],$sheet = 0)
    {
        $this->_excelObj->setActiveSheetIndex($sheet); //设置sheet
        $this->_activeSheet = $this->_excelObj->getActiveSheet(); //获取当前激活状态的sheet
        foreach($header as $k=>$v){
            if(is_array($v)){
                $ff = isset($v[1])?$v[1]:[];//格式化
                $this->_setCellValue($k.'1',$v[0],$ff);
            }else{
                $this->_setCellValue($k.'1',$v);
            }
        }
    }
    /**
     * 设置数据
     * @param array $field 需要显示的字段信息，例如 ['A'=>'name','B'=>['rdate',['datetime'=>'Y-m']]
     * @param $data
     */
    public function setData($field,$data)
    {
        foreach($data as $k=>$v){
            foreach($field as $fk=>$fv){
                $cellkey = $fk.($k+2);
                if(is_array($fv)){//有格式的数据
                    $ff = isset($fv[1])?$fv[1]:[];//格式化
                    $this->_setCellValue($cellkey,$v[$fv[0]],$ff);
                }else{
                    $this->_setCellValue($cellkey,$v[$fv]);
                }
            }
        }
    }
    /**
     * 导出excel
     * @param string $file 导出的文件名
     * @param string $ext 导出的后缀名 xls|xlsx
     */
    public function output($file,$ext = 'xlsx')
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$file.'.'.$ext.'"');
        header('Cache-Control: max-age=0');
        if($ext == 'xlsx'){
            $write = new \PHPExcel_Writer_Excel2007($this->_excelObj);
        }else{
            $write = new \PHPExcel_Writer_Excel5($this->_excelObj);
        }
        $write->save('php://output');
    }
    /**
     * 保存excel
     * @param $file
     * @param string $ext
     */
    public function save($file,$ext = 'xlsx')
    {
        if($ext == 'xlsx'){
            $write = new \PHPExcel_Writer_Excel2007($this->_excelObj);
        }else{
            $write = new \PHPExcel_Writer_Excel5($this->_excelObj);
        }
        $write->save($file.'.xlsx');
    }
}