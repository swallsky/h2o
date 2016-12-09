# Excel的导出使用

**H2O excel** -- 基于phpexcel包封装的常用导出方法

* **excel常规导出**
  
```
例如：
    $obj = new \H2O\office\Phpexcel();
    $obj->export(); //导出初始化
    $obj->setHeader([//设置表头
        'A'	=>	['姓名',['width'=>200,'bold'=>true]], //设置样式
        'B'	=>	'性别',
        'C'	=>	'地址',
        'D'	=>	'出生日期'
    ],0);
    $obj->setData(//设置数据
        [//设置数据索引和格式
	     'A'=>'name',//普通数据索引
	     'B'=>'sex',
	     'C'=>'address',
	     'D'=>['birthday',['datetime'=>'m-d']] //设置数据样式
	],
	[
	     ['name'=>'测试1','sex'=>'男','address'=>'北京33','birthday'=>'1998-09-09'],
	     ['name'=>'测试2','sex'=>'女','address'=>'北京22','birthday'=>'1989-09-12'],
	     ['name'=>'测试3','sex'=>'男','address'=>'北京11','birthday'=>'1979-08-08'],
	     ['name'=>'测试4','sex'=>'男','address'=>'北京22','birthday'=>'1968-01-12']
	]
     );
     $obj->save(APP_PATH.DS.'data'.DS.'setTest');//保存文件
```