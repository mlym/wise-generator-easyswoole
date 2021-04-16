<?php
namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use EasySwoole\ORM\Utility\Schema\Column;

class Add extends MethodAbstract
{
    protected $methodName = 'add';
    protected $methodDescription = '新增数据';

    function addMethodBody()
    {
        $method = $this->method;
        $modelName = $this->getModelName();
        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$data = [

Body;
        $this->chunkTableColumn(function (Column $column, string $columnName) use (&$methodBody) {
            $paramValue = $this->newColumnParam($column);

            if ($column->getIsPrimaryKey() == false && !in_array($columnName,$this->ignoreField)){
                if ($column->isNotNull()) {
                    $paramValue->required = '';
                    $methodBody .= "    '{$columnName}'=>\$param['{$columnName}'],\n";
                } else {
                    $paramValue->optional = '';
                    $methodBody .= "    '{$columnName}'=>\$param['{$columnName}'] ?? '',\n";
                }
                $this->addColumnComment($paramValue);
            }
        });

        $methodBody .= <<<Body
];
\$model = new {$modelName}(\$data);
\$model->save();
return \$this->writeJson(Status::CODE_OK, \$model->toArray(), "操作成功");

Body;

        //配置方法内容
        $method->setBody($methodBody);
    }


    function addComment()
    {
        $this->method->addComment("@throws \Throwable");
    }
}
