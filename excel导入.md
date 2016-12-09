# Excel的导入使用

**H2O excel** -- 基于phpexcel包封装的常用导入方法

* **excel常规导入**

```
例如：
    $file = '/d/s/test.xlsx';//excel地址
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
        exit();
    }
    $data = $obj->GetSimpleData([
        'A' =>  'name',
        'B' =>  'sex',
        'C' =>  'address',
        'D' =>  'job',
        'E' =>  ['rdate','time','Y-m-d H:i:s'] //第一个参数为字段名 第二个为字段类型默认为string
    ]);
    //处理数据
```

* **excel切片导入**

```
例如：
    $file = '/d/s/test.xlsx';//excel文件的地址
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
        exit();
    }
    $queue = $obj->QueueData([
        'A' =>  'name', //定义数据索引
        'B' =>  'sex',
        'C' =>  'address',
        'D' =>  'job',
        'E' =>  ['rdate','time','Y-m-d H:i:s'] //第一个参数为字段名 第二个为字段类型默认为string
    ],10000);
    //循环读取切片信息
    foreach($queue as $q){
        $simpledata = $obj->GetSwpData($q);
        //处理切片数据
    }
```



