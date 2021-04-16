<?php

namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use EasySwoole\ORM\Utility\Schema\Column;

class GetOne extends MethodAbstract
{

    protected $methodName = 'getOne';
    protected $methodDescription = '获取一条数据';

    function addMethodBody()
    {
        $method = $this->method;
        $table = $this->controllerConfig->getTable();
        $modelName = $this->getModelName();
        $responseParamComment = [];
        $this->chunkTableColumn(function (Column $column, string $columnName) use ($table, &$methodBody, &$responseParamComment) {
            $paramValue = $this->newColumnParam($column);
            $responseParamName = "result.{$column->getColumnName()}";
            $responseParamComment[] = "@ApiSuccessParam(name=\"{$responseParamName}\",description=\"{$column->getColumnComment()}\")";
            if ($columnName != $table->getPkFiledName()) {
                return false;
            }
            $paramValue->required = '';
            $this->addColumnComment($paramValue);
            return false;
        });
//        $this->addResponseParamComment($responseParamComment);

        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$model = new {$modelName}();
\$info = \$model->get(['{$table->getPkFiledName()}' => \$param['{$table->getPkFiledName()}']]);
if (\$info) {
    \$this->writeJson(Status::CODE_OK, \$info, "操作成功");
} else {
    \$this->writeJson(Status::CODE_OK, [], '数据不存在');
}
Body;
        $method->setBody($methodBody);

    }

    function addResponseParamComment($responseParamArr)
    {
        foreach ($responseParamArr as $value) {
            $this->method->addComment($value);
        }
    }


    function addComment()
    {
        $this->method->addComment("@throws \Throwable");
    }
}
