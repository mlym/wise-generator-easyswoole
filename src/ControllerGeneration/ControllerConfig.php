<?php
namespace Mlym\CodeGeneration\ControllerGeneration;

use EasySwoole\Command\CommandManager;
use Mlym\CodeGeneration\ModelGeneration\ModelGeneration;
use Mlym\CodeGeneration\ModelGeneration\ModelConfig;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\ORM\Utility\Schema\Table;

class ControllerConfig extends ModelConfig
{
    protected $authSessionName;//额外需要的授权session名称
    protected $modelClass;//model的类名
    protected $fileSuffix='';//文件后缀

    public function __construct(string $modelClass, Table $schemaInfo, $tablePre = '', $nameSpace = "App\\HttpController", $extendClass = AnnotationController::class)
    {
        $this->setModelClass($modelClass);
        $this->setTable($schemaInfo);
        $this->setTablePre($tablePre);
        $this->setNamespace($nameSpace);

        /**
         * 继承Base Controller
         */
        $isExtendBase = array_key_exists('extendBase', CommandManager::getInstance()->getOpts());
        if ($isExtendBase){
            //继承当前路径下的BaseModel
            $this->setExtendClass("{$nameSpace}\\Base");
        }else{
            //默认继承AbstractModel
            $this->setExtendClass($extendClass);
        }
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
    public function getAuthSessionName()
    {
        return $this->authSessionName;
    }

    /**
     * @param mixed $authSessionName
     */
    public function setAuthSessionName($authSessionName): void
    {
        $this->authSessionName = $authSessionName;
    }

}
