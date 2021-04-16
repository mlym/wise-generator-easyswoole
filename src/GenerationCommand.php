<?php

namespace Mlym\CodeGeneration;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\Component\Di;
use EasySwoole\Component\Timer;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\Utility\ArrayToTextTable;
use EasySwoole\Utility\Str;
use Mlym\CodeGeneration\InitBaseClass\Controller\ControllerConfig;
use Mlym\CodeGeneration\InitBaseClass\Controller\ControllerGeneration;
use Mlym\CodeGeneration\InitBaseClass\Model\ModelConfig;
use Mlym\CodeGeneration\InitBaseClass\Model\ModelGeneration;
use Mlym\CodeGeneration\InitBaseClass\UnitTest\UnitTestConfig;
use Mlym\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Scheduler;


class GenerationCommand implements CommandInterface
{
    public function commandName(): string
    {
        return "generation";
    }

    public function desc(): string
    {
        return 'Code auto generation tool';
    }


    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('init', 'initialization');
        $commandHelp->addAction('all', 'specify build');
        $commandHelp->addActionOpt('--tableName', 'specify table name');
        $commandHelp->addActionOpt('--modelPath', 'specify model path');
        $commandHelp->addActionOpt('--controllerPath', 'specify controller path');
        $commandHelp->addActionOpt('--unitTestPath', 'specify unit-test path');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0);
        $result = new Result();
        $run = new Scheduler();
        $run->add(function () use (&$result, $action) {
            switch ($action) {
                case 'init':
                    $result = $this->init();
                    break;
                case 'all':
                    $result = $this->all();
                    break;
                default:
                    $result = CommandManager::getInstance()->displayCommandHelp($this->commandName());
                    break;
            }
            Timer::getInstance()->clearAll();
        });
        $run->start();
        return $result . PHP_EOL;
    }


    /**
     * @return ArrayToTextTable
     * @throws \Exception
     */
    protected function init()
    {
        $modulePath = CommandManager::getInstance()->getOpt('modulePath') ?? '';
        if ($modulePath) {
            Str::startsWith($modulePath, "\\") || $modulePath = "\\" . $modulePath;
        }
        var_dump($modulePath);
        $table = [];
        $table[0] = ['className' => 'Model', 'filePath' => $this->generationBaseModel($modulePath)];
        $table[1] = ['className' => 'Controller', 'filePath' => $this->generationBaseController($modulePath)];
        $table[2] = ['className' => 'UnitTest', 'filePath' => $this->generationBaseUnitTest($modulePath)];

        return new ArrayToTextTable($table);
    }

    /**
     * @return ArrayToTextTable|string
     * @throws \Throwable
     */
    protected function all()
    {

        /**
         * 获取参数
         */
        $tableName = CommandManager::getInstance()->getOpt('tableName');
        if (empty($tableName)) {
            return Color::error('table not empty');
        }
        $modelPath = CommandManager::getInstance()->getOpt('modelPath');
        $controllerPath = CommandManager::getInstance()->getOpt('controllerPath');
        $unitTestPath = CommandManager::getInstance()->getOpt('unitTestPath');
        /**
         * 数据库连接
         */
        $connection = $this->getConnection();
        /**
         * 构建代码生成类，生成表对象
         */
        $codeGeneration = new CodeGeneration($tableName, $connection);
        $codeGeneration->setDescription(CommandManager::getInstance()->getOpt('description') ?? '');

        /**
         * 通过DI获取控制器、模型、单元测试、根路径等信息进行配置（在bootstrap注入）
         */
        $this->trySetDiGenerationPath($codeGeneration);

        $table = [];
        if ($modelPath) {
            $filePath = $codeGeneration->generationModel($modelPath);
            $table[] = ['className' => 'Model', "filePath" => $filePath];
        } else {
            return Color::error('Model path must be specified');
        }

        if ($controllerPath) {
            $filePath = $codeGeneration->generationController($controllerPath);
            $table[] = ['className' => 'Controller', "filePath" => $filePath];
        }
        if ($unitTestPath) {
            $filePath = $codeGeneration->generationUnitTest($unitTestPath);
            $table[] = ['className' => 'UnitTest', "filePath" => $filePath];
        }

        return new ArrayToTextTable($table);
    }

    /**
     * @return Connection
     * @throws \Throwable
     */
    protected function getConnection(): Connection
    {
        $connection = Di::getInstance()->get('CodeGeneration.connection');
        if ($connection instanceof Connection) {
            return $connection;
        } elseif (is_array($connection)) {
            $mysqlConfig = new \EasySwoole\ORM\Db\Config($connection);
            $connection = new Connection($mysqlConfig);
            return $connection;
        } elseif ($connection instanceof \EasySwoole\ORM\Db\Config) {
            $connection = new Connection($connection);
            return $connection;
        }
        return null;
    }

    /**
     * @param CodeGeneration $codeGeneration
     * @throws \Throwable
     */
    protected function trySetDiGenerationPath(CodeGeneration $codeGeneration)
    {
        $modelBaseNameSpace = Di::getInstance()->get('CodeGeneration.modelBaseNameSpace');
        $controllerBaseNameSpace = Di::getInstance()->get('CodeGeneration.controllerBaseNameSpace');
        $unitTestBaseNameSpace = Di::getInstance()->get('CodeGeneration.unitTestBaseNameSpace');
        $rootPath = Di::getInstance()->get('CodeGeneration.rootPath');
        if ($modelBaseNameSpace) {
            $codeGeneration->setModelBaseNameSpace($modelBaseNameSpace);
        }
        if ($controllerBaseNameSpace) {
            $codeGeneration->setControllerBaseNameSpace($controllerBaseNameSpace);
        }
        if ($unitTestBaseNameSpace) {
            $codeGeneration->setUnitTestBaseNameSpace($unitTestBaseNameSpace);
        }
        if ($unitTestBaseNameSpace) {
            $codeGeneration->setRootPath($rootPath);
        }
    }


    /**
     * @param $modulePath
     * @return bool|int
     * @throws \Exception
     */
    protected function generationBaseController($modulePath)
    {
        $config = new ControllerConfig('Base', "App\\HttpController{$modulePath}");
        $config->setExtendClass(AnnotationController::class);
        $generation = new ControllerGeneration($config);
        return $generation->generate();
    }

    /**
     * @param $modulePath
     * @return bool|int
     * @throws \Exception
     */
    protected function generationBaseUnitTest($modulePath)
    {
        $config = new UnitTestConfig("BaseTest", "UnitTest{$modulePath}");
        $config->setExtendClass(TestCase::class);
        $generation = new UnitTestGeneration($config);
        return $generation->generate();
    }

    /**
     * @param $modulePath
     * @return mixed
     * @throws \Exception
     */
    protected function generationBaseModel($modulePath)
    {
        $config = new ModelConfig("BaseModel", "App\\Model{$modulePath}");
        $config->setExtendClass(AbstractModel::class);
        $generation = new ModelGeneration($config);
        return $generation->generate();
    }

}
