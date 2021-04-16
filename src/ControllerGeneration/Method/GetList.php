<?php
namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use EasySwoole\ORM\Utility\Schema\Column;

class GetList extends MethodAbstract
{

    protected $methodName = 'getList';
    protected $methodDescription = '获取数据列表';


    function addMethodBody()
    {
        $method = $this->method;
        $modelName = $this->getModelName();

        //新增page参数注解
        $method->addComment("@Param(name=\"pageNo\", from={GET,POST}, alias=\"页数\", optional=\"\", defaultValue=\"1\")");
        $method->addComment("@Param(name=\"pageSize\", from={GET,POST}, alias=\"每页总数\", optional=\"\", defaultValue=\"10\")");

        $responseParamComment = [];
        $this->chunkTableColumn(function (Column $column, string $columnName) use (&$responseParamComment) {
            $responseParamName = "result[].{$columnName}";
            $responseParamComment[] = "@ApiSuccessParam(name=\"{$responseParamName}\",description=\"{$column->getColumnComment()}\")";
            return false;
        });
//        $this->addResponseParamComment($responseParamComment);

        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$pageNo = (int)(\$param['pageNo'] ?? 1);
\$pageSize = (int)(\$param['pageSize'] ?? 20);
\$model = new {$modelName}();
\$data = \$model->getList(\$pageNo, \$pageSize);
\$this->writeJson(Status::CODE_OK, \$data, '操作成功');
Body;
        $method->setBody($methodBody);
    }


    function addResponseParamComment($responseParamArr){
        foreach ($responseParamArr as  $value){
            $this->method->addComment($value);
        }
    }

    function addComment()
    {
        $this->method->addComment("@throws \Throwable");
    }
}
