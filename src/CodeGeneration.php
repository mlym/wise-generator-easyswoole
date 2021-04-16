<?php

namespace Mlym\CodeGeneration;


use EasySwoole\Command\CommandManager;
use EasySwoole\Mysqli\QueryBuilder;
use Mlym\CodeGeneration\ControllerGeneration\ControllerConfig;
use Mlym\CodeGeneration\ControllerGeneration\ControllerGeneration;
use Mlym\CodeGeneration\ModelGeneration\ModelGeneration;
use Mlym\CodeGeneration\ModelGeneration\ModelConfig;
use Mlym\CodeGeneration\UnitTest\UnitTestConfig;
use Mlym\CodeGeneration\UnitTest\UnitTestGeneration;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\ORM\Db\Connection;
use Mlym\CodeGeneration\Unity\TableObjectGenerationSub;
use PHPUnit\Framework\TestCase;

class CodeGeneration
{
    protected $schemaInfo;
    protected $modelGeneration;
    protected $controllerGeneration;
    protected $modelBaseNameSpace = "App\\Model";
    protected $controllerBaseNameSpace = "App\\HttpController";
    protected $unitTestBaseNameSpace = "UnitTest";
    protected $rootPath;
    protected $moduleName;

    /**
     * CodeGeneration constructor.
     * @param string $tableName
     * @param Connection $connection
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    function __construct(string $tableName, Connection $connection)
    {
        $tableObjectGeneration = new TableObjectGenerationSub($connection, $tableName);
        /**
         * ORM的TableObjectGeneration类在生成Table时，缺少对IsNotNull的设置
         * 以下作为补充
         */
        $schemaInfo = $tableObjectGeneration->generationTable();
        $this->schemaInfo = $schemaInfo;

        /**
         * 获取表备注
         */
        $sql = 'select TABLE_COMMENT from information_schema.TABLES where TABLE_SCHEMA= ? and  TABLE_NAME=?';
        $query = (new QueryBuilder())->raw($sql,[$connection->getConfig()->getDatabase(),$tableName]);
        $tableComment = $connection->defer()->query($query)->getResultScalar('TABLE_COMMENT') ?? '';
        $this->schemaInfo->setTableComment($tableComment);
    }

    function getModelGeneration($path, $tablePre = '', $extendClass = \EasySwoole\ORM\AbstractModel::class): ModelGeneration
    {
        $modelConfig = new ModelConfig($this->schemaInfo, $tablePre, "{$this->modelBaseNameSpace}{$path}", $extendClass);
        $modelConfig->setRootPath($this->getRootPath());
        $modelConfig->setModuleName($this->getModuleName());
        $modelGeneration = new ModelGeneration($modelConfig);
        $this->modelGeneration = $modelGeneration;
        return $modelGeneration;
    }

    function getControllerGeneration(ModelGeneration $modelGeneration, $path, $tablePre = '', $extendClass = AnnotationController::class): ControllerGeneration
    {
        $controllerConfig = new ControllerConfig($modelGeneration->getConfig()->getNamespace() . '\\' . $modelGeneration->getClassName(), $this->schemaInfo, $tablePre, "{$this->controllerBaseNameSpace}{$path}", $extendClass);
        $controllerConfig->setRootPath($this->getRootPath());
        $controllerConfig->setModuleName($this->getModuleName());
        $controllerGeneration = new ControllerGeneration($controllerConfig);
        $this->controllerGeneration = $controllerGeneration;
        return $controllerGeneration;
    }

    function getUnitTestGeneration(ModelGeneration $modelGeneration, ControllerGeneration $controllerGeneration, $path, $tablePre = '', $extendClass = TestCase::class): UnitTestGeneration
    {
        $controllerConfig = new UnitTestConfig($modelGeneration->getConfig()->getNamespace() . '\\' . $modelGeneration->getClassName(), $controllerGeneration->getConfig()->getNamespace() . '\\' . $controllerGeneration->getClassName(), $this->schemaInfo, $tablePre, "{$this->unitTestBaseNameSpace}{$path}", $extendClass);
        $controllerConfig->setRootPath($this->getRootPath());
        $controllerConfig->setModuleName($this->getModuleName());
        $unitTestGeneration = new UnitTestGeneration($controllerConfig);
        return $unitTestGeneration;
    }

    function generationModel($path, $tablePre = '', $extendClass = \EasySwoole\ORM\AbstractModel::class)
    {
        $modelGeneration = $this->getModelGeneration($path, $tablePre, $extendClass);
        $result = $modelGeneration->generate();
        return $result;
    }

    function generationController($path, ?ModelGeneration $modelGeneration = null, $tablePre = '', $extendClass = AnnotationController::class)
    {
        $modelGeneration = $modelGeneration ?? $this->modelGeneration;
        $controllerGeneration = $this->getControllerGeneration($modelGeneration, $path, $tablePre, $extendClass);
        $result = $controllerGeneration->generate();
        return $result;
    }

    function generationUnitTest($path, ?ModelGeneration $modelGeneration = null, ?ControllerGeneration $controllerGeneration = null, $tablePre = '', $extendClass = TestCase::class)
    {
        $modelGeneration = $modelGeneration ?? $this->modelGeneration;
        $controllerGeneration = $controllerGeneration ?? $this->controllerGeneration;
        $controllerGeneration = $this->getUnitTestGeneration($modelGeneration, $controllerGeneration, $path, $tablePre, $extendClass);
        $result = $controllerGeneration->generate();
        return $result;
    }

    /**
     * @return string
     */
    public function getModelBaseNameSpace(): string
    {
        return $this->modelBaseNameSpace;
    }

    /**
     * @param string $modelBaseNameSpace
     */
    public function setModelBaseNameSpace(string $modelBaseNameSpace): void
    {
        $this->modelBaseNameSpace = $modelBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getControllerBaseNameSpace(): string
    {
        return $this->controllerBaseNameSpace;
    }

    /**
     * @param string $controllerBaseNameSpace
     */
    public function setControllerBaseNameSpace(string $controllerBaseNameSpace): void
    {
        $this->controllerBaseNameSpace = $controllerBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getUnitTestBaseNameSpace(): string
    {
        return $this->unitTestBaseNameSpace;
    }

    /**
     * @param string $unitTestBaseNameSpace
     */
    public function setUnitTestBaseNameSpace(string $unitTestBaseNameSpace): void
    {
        $this->unitTestBaseNameSpace = $unitTestBaseNameSpace;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        if (empty($this->rootPath)) {
            $this->rootPath = getcwd();
        }
        return $this->rootPath;
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param mixed $moduleName
     */
    public function setModuleName($moduleName): void
    {
        $this->moduleName = $moduleName;
    }




}
