### 配置参数

---

* **version**

系统版本号，可根据具体的业务设置版本号，并不一定要跟具体业务版本号一致，该版本在系统升级和更新的时候非常重，必须填写，在migrate时，都是系统版本号来划分全量或者增量更新数据库信息

```
'version' => '0.0.9' //系统版本号
```

* **basePath**

程序存放的主目录，存放controller、model、view等程序位置

```
'basePath' => dirname(__DIR__).DS.'unit' //主程序目录
```

* **runenv**

环境设置，共可以设置三种环境，分别为\[prod:生产环境、dev:开发环境、test:测试环境\]

```
'runenv' => 'prod' //生产环境
```

* **boot**

引导程序前置模块，在所有应用程序之前执行，可以设计成用来记录日志信息等功能

```
'boot' => '\app\unit\controllers\test.boot' //应用启动之前执行的
```

* **db**

数据库信息设置,支持多库操作，默认为db库，数据库暂只支持utf8编码

```
'db' =>    [
  'db'  => [//默认库
      'host'      =>  'localhost', //主机地址
      'username'  =>    'dbuser', //用户名
      'password'  =>    '123', //密码
      'dbname'    =>    'detest' //数据库名
   ],
  'test'  => [//其他库操作
      'host'      =>  'localhost', //主机地址
      'username'  =>    'test', //用户名
      'password'  =>    '123', //密码
      'dbname'    =>    'test' //数据库名
   ],
] //数据库配置信息
```

* **debug**

debug性能时，查看程序执行情况 例如运行时间、消耗内存等，该信息不会直接输出到页面上，Web应用可以在runtime/web/debugger可以查看到，cli命令下查看runtime/console/debugger，参数为布尔类型，只支持true、false

```
'debug' => true
```

* **defaultLayout**

Web应用专属，默认布局模块设置，主要用于相应布局相同的模块间，作为基础模块设置

```
'defaultLayout' => 'layout.index' //默认布局
```

* **request**

Web应用专属配置，路由规则设置，通过request参数可实现url重写、优化！

> 通用模式

默认模式为通用模式，无须设置request参数，直接访问模块和方法

```
url1:site.index?sid=128989899
url2:main.index
url3:page.test
```

> 自定义模式

例：./test.hello?id=12&t=1&sky=123 转换为 ./test/12/1/123

```
'request' => [
  'route'    =>  [
      'test'  =>  ['test.hello','id','t','sky']
  ]
]
```

> 重写模式

通过指写应用程序来控制url的重写规则，具体设置格式如下：

```
'request' => '\app\unit\controllers\request.rule'
```



