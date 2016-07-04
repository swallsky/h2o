<?php
/**
 * 基于phpExcel的excel导入、导出
 * @category   H2O
 * @package    phpoffice
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\phpoffice;
//phpexcel的包目录
define('PHPEXCEL_DIR',VENDOR_PATH.DS.'phpoffice'.DS.'phpexcel'.DS);
//phpexcel cache 目录
define('PHPEXCEL_CACHE_DIR',APP_RUNTIME.DS.'phpexcel');
/** Include PHPExcel_IOFactory */
require_once(PHPEXCEL_DIR .'Classes'.DS.'PHPExcel'.DS.'IOFactory.php');

class Phpexcel
{
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
     * @param $file 导入的excel文件路径
     * @param $sheetnum 读取工作表顺序
     */
    public function GetObj($file,$sheetnum = 0)
    {
        $this->_oexcel = \PHPExcel_IOFactory::load($file);
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
     * 返回excel数据
     * @return array
     */
    public function GetData()
    {
        $data = [];
        for ($row = 2; $row <= $this->_rows; $row++){//行数是以第1行开始
            $tmp = [];
            for ($column = 'A'; $column <= $this->_cols; $column++) {//列数是以A列开始
                $tmp[$column] = $this->_sheet->getCell($column.$row)->getValue();
            }
            $data[$row-2] = $tmp;
        }
        return $data;
    }
}