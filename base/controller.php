<?php
/**
 * 所有控制器的基类
 * @category   H2O
 * @package    base
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\base;
use H2O;
abstract class Controller
{
	/**
	 * 查找运行对应的Action方法
	 * @param string $action
	 */
	public function runAction($action)
	{
		$action = 'act'.ucfirst($action);
		$o = new static(); //初始化对应的类
		if(method_exists($o,$action)){
			call_user_func([$o,$action]); //执行对应的方法
		}else{
			throw new Exception('No action method!',get_class($o).'->'.$action);
		}
	}
}