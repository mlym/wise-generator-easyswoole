<?php
namespace Mlym\CodeGeneration\ControllerGeneration\Method;


use EasySwoole\ORM\Utility\Schema\Column;

class Delete extends MethodAbstract
{

    protected $methodName = 'delete';
    protected $methodDescription = '删除数据';

    function addMethodBody()
    {
        $method = $this->method;

        $modelName = $this->getModelName();
        $table = $this->controllerConfig->getTable();

        $this->chunkTableColumn(function (Column $column, string $columnName) use ($table, &$methodBody) {
            $paramValue = $this->newColumnParam($column);
            if ($columnName != $table->getPkFiledName()) {
                return false;
            }
            $paramValue->required = '';
            $paramValue->name = 'ids';
            $this->addColumnComment($paramValue);
            return true;
        });

        $methodBody = <<<Body
\$param = ContextManager::getInstance()->get('param');
\$model = new {$modelName}();
\$ids = explode(',',\$param['ids']?? '');
empty(\$ids) || \$model->destroy(\$ids);
\$this->writeJson(Status::CODE_OK, [], "删除成功.");
Body;
        $method->setBody($methodBody);

    }


    function addComment()
    {
        $this->method->addComment("@throws \Throwable");
    }
}
