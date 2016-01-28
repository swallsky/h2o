<?php
/**
 * 文件和文件夹相关的助手类
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class File
{
	/**
	 * 创建新的文件夹
	 * @param string $path 要创建的目录
	 * @param integer $mode 创建目录权限
	 * @param boolean $recursive 是否要创建父级目录
	 * @return boolean 创建是否成功
	 */
	public static function createDirectory($path, $mode = 0775, $recursive = true)
	{
		if (is_dir($path)) {
			return true;
		}
		$parentDir = dirname($path);
		if ($recursive && !is_dir($parentDir)) {
			static::createDirectory($parentDir, $mode, true);
		}
		try {
			$result = mkdir($path, $mode);
			chmod($path, $mode);
		} catch (\Exception $e) {
			throw new \Exception("Failed to create directory '$path': " . $e->getMessage(), $e->getCode(), $e);
		}
		return $result;
	}
	/**
	 * 复制整个目录,包含子目录和文件
	 * @param string $src 源目录
	 * @param string $dst 目标目录
	 * @param integer $mode 目录权限
	 */
	public static function copyDirectory($src, $dst, $mode = 0775)
	{
		if (!is_dir($dst)) {
			static::createDirectory($dst, $mode, true);
		}
		$handle = opendir($src);
		if ($handle === false) {
			throw new \Exception("Unable to open directory: $src");
		}
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$from = $src . DS . $file;
			$to = $dst . DS . $file;
			if (is_file($from)) {
				copy($from, $to);
			} else {
				static::copyDirectory($from, $to, $mode);
			}
		}
		closedir($handle);
	}
	/**
	 * 递归删除该目录
	 * @param string $dir 要删除的目录
	 */
	public static function removeDirectory($dir)
	{
		if (!is_dir($dir)) {
			return;
		}
		if (!is_link($dir)) {
			if (!($handle = opendir($dir))) {
				return;
			}
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}
				$path = $dir . DS . $file;
				if (is_dir($path)) {
					static::removeDirectory($path);
				} else {
					unlink($path);
				}
			}
			closedir($handle);
		}
		if (is_link($dir)) {
			unlink($dir);
		} else {
			rmdir($dir);
		}
	}
	/**
	 * 写入文件，默认文件内容追加到原内容之后
	 * @param string $file 文件名
	 * @param string $content 写入内容
	 * @param bool $append 是否将内容追加到文件末尾
	 */
	public static function write($file,$content,$append = true)
	{
		$dir = dirname($file);
		if(!is_dir($dir)){
			static::createDirectory($dir,0775, true);
		}
		try{
			if($append){
				file_put_contents($file,$content,FILE_APPEND);
			}else{
				file_put_contents($file,$content);
			}
		} catch (\Exception $e) {
			throw new \Exception("Failed to write file '$file': " . $e->getMessage(), $e->getCode(), $e);
		}
	}
}