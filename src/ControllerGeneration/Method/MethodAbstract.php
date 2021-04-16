<?php
namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use Mlym\CodeGeneration\ControllerGeneration\ControllerConfig;
use Mlym\CodeGeneration\ControllerGeneration\ControllerGeneration;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\ORM\Utility\Schema\Column;

abstract class MethodAbstract extends \Mlym\CodeGeneration\ClassGeneration\MethodAbstract
{
    /**
     * @var \Nette\PhpGenerator\Method $method
     */
    protected $method;
    /**
     * @var ControllerConfig
     */
    protected $controllerConfig;

    protected $methodName = 'methodName';
    protected $methodDescription = '这是生成的测试方法介绍';
    protected $responseParam = [
        'code'   => '状态编码',
        'data' => '数据集合',
        'msg'    => '提示信息',
    ];
    protected $authParam = null;
    protected $methodAllow = "GET,POST";
    protected $responseSuccessText = '{"code":200,"result":[],"msg":"操作成功"}';
    protected $responseFailText = '{"code":400,"result":[],"msg":"操作失败"}"}';
    protected $ignoreField = ['create_time','update_time','createAt','updateAt','createTime','updateTime'];

    function __construct(ControllerGeneration $classGeneration)
    {
        $this->classGeneration = $classGeneration;
        $method = $classGeneration->getPhpClass()->addMethod($this->getMethodName());
        $this->method = $method;
        $this->controllerConfig = $classGeneration->getConfig();
        if ($this->controllerConfig instanceof ControllerConfig){
            $this->authParam = $this->controllerConfig->getAuthSessionName();
        }
    }

    function run(){
        $this->addRequestComment();
//        $this->addResponseComment();
        $this->addMethodBody();
        $this->addComment();
    }


    protected function addRequestComment()
    {
        $realTableName = $this->controllerConfig->getRealTableName();
        $apiUrl = $this->getApiUrl();
        $method = $this->method;
        $methodName = $this->methodName;

        //配置基础注释
        $method->addComment("@Api(name=\"{$methodName}\",path=\"{$apiUrl}/{$realTableName}/{$methodName}\")");
        $method->addComment("@ApiDescription(\"{$this->methodDescription}\")");
        $method->addComment("@Method(allow={{$this->methodAllow}})");
        if ($this->authParam) {
            $method->addComment("@Param(name=\"{$this->authParam}\", from={COOKIE,GET,POST}, alias=\"权限验证token\" required=\"\")");
        }
        $method->addComment("@InjectParamsContext(key=\"param\")");
    }


    protected function addResponseComment()
    {
        $method = $this->method;
        foreach ($this->responseParam as $name => $description) {
            $method->addComment("@ApiSuccessParam(name=\"{$name}\",description=\"{$description}\")");
        }
        $method->addComment("@ApiSuccess({$this->responseSuccessText})");
        $method->addComment("@ApiFail({$this->responseFailText})");
    }

    protected function getApiUrl()
    {
        $baseNamespace = $this->controllerConfig->getNamespace();
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $baseNamespace);
        return $apiUrl;
    }

    protected function getModelName()
    {
        $modelNameArr = (explode('\\', $this->controllerConfig->getModelClass()));
        $modelName = end($modelNameArr);
        return $modelName;
    }


    protected function addColumnComment(Param $param)
    {
        $method = $this->method;
        $commentStr = "@Param(name=\"{$param->name}\"";
        $arr = ['alias', 'description', 'lengthMax', 'required', 'optional', 'defaultValue'];
        foreach ($arr as $value) {
            if ($param->$value !== null) {
                $commentStr .= ",$value=\"{$param->$value}\"";
            }
        }
        $commentStr .= ")";
        $method->addComment($commentStr);
    }

    protected function newColumnParam(Column $column)
    {
        $columnName = $column->getColumnName();
        $columnComment = $column->getColumnComment();
        $paramValue = new Param();
        $paramValue->name = $columnName;
        $paramValue->alias = explode(',',$columnComment)[0];
        $paramValue->description = $columnComment;
        $paramValue->lengthMax = $column->getColumnLimit();
        $paramValue->defaultValue = $column->getDefaultValue();
        return $paramValue;
    }

    protected function chunkTableColumn(callable $callback)
    {
        $table = $this->controllerConfig->getTable();
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getColumnName();
            $result = $callback($column, $columnName);
            if ($result ===true){
                break;
            }
        }
    }

    protected function filterField(){

    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }



}
