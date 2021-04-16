<?php
namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use EasySwoole\ORM\Utility\Schema\Column;

class Edit extends MethodAbstract
{

    protected $methodName = 'edit';
    protected $methodDescription = '更新数据';

    function addMethodBody()
    {
        $method = $this->method;
        $modelName = $this->getModelName();
        $table = $this->controllerConfig->getTable();

        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$model = new {$modelName}();
\$info = \$model->get(['{$table->getPkFiledName()}' => \$param['{$table->getPkFiledName()}']]);
if (empty(\$info)) {
    \$this->writeJson(Status::CODE_BAD_REQUEST, [], '该数据不存在');
    return false;
}
\$updateData = [];
Body;
        $this->chunkTableColumn(function (Column $column, string $columnName) use ($table, &$methodBody) {
            $paramValue = $this->newColumnParam($column);

            if (!in_array($columnName,$this->ignoreField)){
                if ($columnName == $table->getPkFiledName()) {
                    $paramValue->required = '';
                } else {
                    $methodBody .= "\$updateData['{$columnName}']=\$param['{$columnName}'] ?? \$info->{$columnName};\n";
                    $paramValue->optional = '';
                }
                $this->addColumnComment($paramValue);
            }
        });

        $methodBody .= <<<Body
\$info->update(\$updateData);
return \$this->writeJson(Status::CODE_OK, \$info, "操作成功");

Body;
        $method->setBody($methodBody);

    }


    function addComment()
    {
        $this->method->addComment("@throws \Throwable");
    }
}
