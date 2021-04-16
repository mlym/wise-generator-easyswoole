<?php
namespace Mlym\CodeGeneration\UnitTest;

use Mlym\CodeGeneration\ModelGeneration\ModelConfig;
use EasySwoole\DDL\Blueprint\Table;
use PHPUnit\Framework\TestCase;

class UnitTestConfig extends ModelConfig
{
    protected $modelClass;//model的类名
    protected $controllerClass;//controller的类名
    protected $fileSuffix = 'Test';//文件生成后缀

    public function __construct($modelClass, $controllerClass, Table $schemaInfo, $tablePre = '', $nameSpace = "UnitTest", $extendClass = TestCase::class)
    {
        $this->setModelClass($modelClass);
        $this->setControllerClass($controllerClass);
        $this->setTable($schemaInfo);
        $this->setTablePre($tablePre);
        $this->setNamespace($nameSpace);
        $this->setExtendClass($extendClass);

    }

    /**
     * @return mixed
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @param mixed $modelClass
     */
    public function setModelClass($modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return mixed
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @param mixed $controllerClass
     */
    public function setControllerClass($controllerClass): void
    {
        $this->controllerClass = $controllerClass;
    }

}
