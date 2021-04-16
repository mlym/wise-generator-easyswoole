<?php
namespace Mlym\CodeGeneration\UnitTest\Method;


use Mlym\CodeGeneration\Unity\Unity;

class GetOne extends UnitTestMethod
{
    protected $methodName = 'testGetOne';
    protected $actionName = 'getOne';
    function addMethodBody()
    {
        $method = $this->method;
        $body = <<<BODY
\$data = [];

BODY;
        $body .= $this->getTableTestData('data');

        $modelName = Unity::getModelName($this->classGeneration->getConfig()->getModelClass());
        $body .= <<<BODY
\$model = new {$modelName}();
\$model->data(\$data)->save();    

\$data = [];
\$data['{$this->classGeneration->getConfig()->getTable()->getPkFiledName()}'] = \$model->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()};
\$response = \$this->request('{$this->actionName}',\$data);
\$model->destroy(\$model->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()});

//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));

BODY;
        $method->setBody($body);
    }
}
