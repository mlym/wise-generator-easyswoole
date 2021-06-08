# wise-generator-easyswoole
基于EasySwoole的code-generation改写，主要应用与内部系统。基本上所有类都有所调整，同时也完善了一些功能。

## 版本说明
[版本说明](https://github.com/mlym/wise-generator-easyswoole/wiki/%E7%89%88%E6%9C%AC%E8%BF%AD%E4%BB%A3)

# code-generation
使用命令行,一键生成业务通用代码,支持代码如下:
- 一键生成项目初始化 baseController,baseModel,baseUnitTest.支持自定义ModulePath
- 一键生成 Model ,自带属性注释，支持自动识别autoTimeStamp、创建时间、更新时间
- 一键生成 CURD控制器（add/edit/getOne/getList/delete）
- 一键生成 控制器单元测试用例


## 安装

```bash
composer require mlym/easyswoole-code-generation
```

## 改善内容
1. Contorller的@ApiGroupDescription可以正确读取表的COMMENT信息
2. Controller和Model可以通过--description 增加功能说明
3. Controller和Model可以继承同目录的Base类（先通过初始化`init --modulePath=\\模块目录` 生成Base类）
4. Controller移除响应注解，由于文档自动生成与实际需求偏差较大，移除了所有Response参数说明
5. Controller修复注解问题，有效区分@param的alias和description
6. Controller修复注解问题，修复所有字段都是required=""，导致必须填写的问题
7. Controller移除部分请求注解，如add时ID必填、create_time必填等情况
8. Controller方法参数调整，为了统一前后端分页字段，将page改为pageNo
9. Controller和Model的代码简化
10. Model支持自动识别$autoTimeStamp、$createTime、$updateTime
11. Model支持自动创建设置器
12. 增加Options



## 使用

### 1. DI注入

在`bootstrap事件`Di注入MySQL配置项:

```php
<?php

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',$mysqlConfig);

//注入执行目录项,后面的为默认值,initClass不能通过注入改变目录
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.modelBaseNameSpace',"App\\Model");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.controllerBaseNameSpace',"App\\HttpController");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.rootPath',getcwd());
```

执行命令:

```bash
php vendor/bin/wise-generator-easyswoole
```

### 2.初始化基础类:

```bash
php vendor/bin/wise-generator-easyswoole init

无参数示例：
┌────────────┬─────────────────────────────────────────────────────────────────────────────────────────┐
│ className  │                                        filePath                                         │
├────────────┼─────────────────────────────────────────────────────────────────────────────────────────┤
│ Model      │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/Model/BaseModel.php     │
├────────────┼─────────────────────────────────────────────────────────────────────────────────────────┤
│ Controller │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/HttpController/Base.php │
├────────────┼─────────────────────────────────────────────────────────────────────────────────────────┤
│ UnitTest   │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/UnitTest/BaseTest.php       │
└────────────┴─────────────────────────────────────────────────────────────────────────────────────────┘
```

参数：
- --modulePath 模块路径
```bash
php vendor/bin/wise-generator-easyswoole init --modulePath=\\admin

参数示例：
┌────────────┬───────────────────────────────────────────────────────────────────────────────────────────────┐
│ className  │                                           filePath                                            │
├────────────┼───────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model      │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/Model/admin/BaseModel.php     │
├────────────┼───────────────────────────────────────────────────────────────────────────────────────────────┤
│ Controller │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/HttpController/admin/Base.php │
├────────────┼───────────────────────────────────────────────────────────────────────────────────────────────┤
│ UnitTest   │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/UnitTest/admin/BaseTest.php       │
└────────────┴───────────────────────────────────────────────────────────────────────────────────────────────┘

```


### 3.自定义业务模块代码:

```bash
php vendor/bin/wise-generator-easyswoole all
```

参数：
- --tableName 必须指定  
- --modelPath 必须指定 模型   
- --controllerPath 控制器  
- --unitTestPath 单元测试
- --extendBase 继承同目录Base基类，不需要指定具体值
- --description 控制器和模型的描述

示例：

```bash
$ php vendor/bin/wise-generator-easyswoole all --tableName=mw_user --controllerPath=\\admin --modelPath=\\admin --extendBase --description=用户模块
┌────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ className  │                                              filePath                                              │
├────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model      │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/Model/admin/MwUserModel.php     │
├────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Controller │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/HttpController/admin/MwUser.php │
└────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────┘```

### 4.生成Json中间件
```bash
php vendor/bin/wise-generator-easyswoole json --tableName=mw_test
┌─────────────┬────────────────────────────────────────────────────────────────────────────────────────┐
│    Table    │                                     MiddleFilePath                                     │
├─────────────┼────────────────────────────────────────────────────────────────────────────────────────┤
│ mw_test_one │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/json_file/mw_test_one.json │
└─────────────┴────────────────────────────────────────────────────────────────────────────────────────┘
```
参数：
- --tableName 必须指定
