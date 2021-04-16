# easyswoole-code-generation
基于EasySwoole的code-generation改写，主要应用与内部系统。基本上所有类都有所调整，同时也完善了一些功能。

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
php vendor/bin/mlym-easyswoole-code-generator
```

### 2.初始化基础类:

```bash
php vendor/bin/mlym-easyswoole-code-generator init

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
php vendor/bin/mlym-easyswoole-code-generator init --modulePath=\\admin

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
php vendor/bin/mlym-easyswoole-code-generator all
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
$ php vendor/bin/mlym-easyswoole-code-generator all --tableName=mw_user --controllerPath=\\admin --modelPath=\\admin --extendBase --description=用户模块
┌────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ className  │                                              filePath                                              │
├────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model      │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/Model/admin/MwUserModel.php     │
├────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Controller │ /Users/ryan/Desktop/src/composer/easyswoole-code-generation/App/HttpController/admin/MwUser.php │
└────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────┘```

