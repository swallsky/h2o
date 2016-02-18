<?php
/**
 * 数据迁移程序
 * @category   H2O
 * @package    db
 * @author     Xujinzhang <xjz1688@163.com>
 * @version    0.1.0
 */
namespace H2O\console;
use H2O\helpers\Stdout;
class Migrate
{
	/**
	 * 创建迁移
	 */
	public function create()
	{
		$request = \H2O::getContainer('request'); //控制台请求
		$params = $request->getParams();
		if(!empty($params['version'])){//初始化
			//TODO
			return 'Migrate '.$params['version'].' create success';
		}
		if(empty($params['version']) && empty($params['dev'])){
			Stdout::title('Warning:');
			Stdout::table([
				['params','error info'],
				['--version','version param is lost!']
			]);
			return Stdout::get();
		}
	}
}