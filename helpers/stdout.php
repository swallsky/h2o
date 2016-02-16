<?php
/**
 * 标准输出 例如文本格式显示，控制台显示，日志记录等
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class Stdout
{
	/**
	 * 换行符
	 */
	public static $br = PHP_EOL;
	/**
	 * @var array 缓存文本信息
	 */
	public static $text = [];
	/**
	 * 需要显示文本信息
	 * @param string $text
	 */
	public static function add($text)
	{
		self::$text[] = $text;
	}
	/**
	 * 设置标题信息
	 * @param string $t
	 */
	public static function title($t)
	{
		self::add(self::$br.$t.self::$br.self::$br);
	}
	/**
	 * 设置表格信息
	 * @param array $data 二维数组
	 * @param int $dislen 间隔距离 以空格为单位
	 * 例如：
	 * \Stdout::table([
	 * 		['h1','h2','h3'],
	 * 		['h1','h2','h3']
	 * 		['h1','h2','h3']
	 * 		['h1','h2','h3']
	 * ]);
	 */
	public static function table($data,$dislen = 4)
	{
		$wcol = []; //记录每列最大字符长度
		//列宽计算
		foreach($data as $row){//循环每行
			foreach ($row as $ck=>$col){
				if(isset($wcol[$ck])){
					$lcol = strlen($col);
					$wcol[$ck] = $lcol>$wcol[$ck]?$lcol:$wcol[$ck];
				}else{
					$wcol[$ck] = strlen($col);
				}
			}
		}
		$td = []; //单元格信息
		//格式显示
		foreach($data as $rk=>$row){
			if($rk==0){//第一列 加表头标识
				$row[0] = '- '.$row[0];
			}
			foreach ($row as $ck=>$col){
				if($rk>0) $col = $ck==0?'    '.$col:$col; //内容列缩进 缩进4个空格
				$td[] = $col.str_repeat(' ', $wcol[$ck] + $dislen + 4 - strlen($col));
			}
			$td[] = self::$br;
		}
		$td[] = self::$br;
		self::add(join('',$td));
	}
	/**
	 * @return 返回表格信息
	 */
	public static function get()
	{
		return join('',self::$text);
	}
}