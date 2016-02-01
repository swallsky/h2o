<?php
/**
 * 为防止XSS攻击，引入HTMLPurifier净化器
 * @category   H2O
 * @package    helpers
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\helpers;
class HTMLPurifier
{
	/**
	 * 净化字符串
	 * @param string $str 需要净化的字符串
	 * @param array $hpcfg
	 * 例如:
	 	HTMLPurifier::filter($html,[
	 		['HTML','DefinitionID','made by debugged interactive designs'],
	 		['HTML','DefinitionRev',1],
	 		['HTML','TidyLevel','heavy'],
	 		['Core','Encoding','UTF-8'],
	 	]);
	 */
	public static function filter($str,$hpcfg = [])
	{
		$config = \HTMLPurifier_Config::createDefault();
		if(!empty($hpcfg)){
			foreach($hpcfg as $v){
				$config->set($v[0],$v[1],$v[2]); //设置过滤规则
			}
		}
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($str);
	}
}