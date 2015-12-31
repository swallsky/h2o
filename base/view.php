<?php
/**
 * 所有视图的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
class View
{
	/**
	 * @var string 模板目录
	 */
	private $_templatePath;
	/**
	 * @var string 模板文件
	 */
	private $_templateFile;
	/**
	 * 初始化
	 * @param string $tpl 模板文件
	 */
	public function __construct($tpl)
	{
		$this->_templateFile = $tpl;
	}
	/**
	 * 返回模板目录
	 */
	public function getPath()
	{
		return rtrim($this->_templatePath,DIRECTORY_SEPARATOR);
	}
	/**
	 * 设置模板目录
	 * @param string $path 模板目录
	 */
	public function setPath($path)
	{
		$path = \H2O::getAlias($path);
		$this->_templatePath = $path;
	}
	/**
	 * 检查文件是否存在
	 * @param string $tpl 模板文件
	 * @throws Exception
	 */
	private function _checkFile($tpl)
	{
		$tpl = \H2O::getAlias($tpl);
		if(!file_exists($tpl)){
			throw new Exception('View::_checkFile',$tpl.':template is not found!');
		}
	}
	/**
	 * 返回模板文件
	 */
	public function getFile()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->_templateFile;
	}
	/**
	 * 模板解析和渲染
	 * @param array $vars 控制层需要传递给模板的变量
	 */
	public function render($vars)
	{
		$tpl = $this->getFile();
		$this->_checkFile($tpl);
		ob_start();
		foreach($vars as $k=>$v) $$k = $v; //设置模板变量
		include($tpl);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}
}