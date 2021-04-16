<?php
namespace Mlym\CodeGeneration\UnitTest\Method;


use Mlym\CodeGeneration\Unity\Unity;

class Add extends UnitTestMethod
{
    protected $methodName = 'testAdd';
    protected $actionName = 'add';
    function addMethodBody()
    {
        $method = $this->method;
        $body = <<<BODY
\$data = [];

BODY;
        $body .= $this->getTableTestData('data');
        $modelName = Unity::getModelName($this->classGeneration->getConfig()->getModelClass());

        $body .= <<<BODY
\$response = \$this->request('{$this->actionName}',\$data);
\$model = new {$modelName}();
\$model->destroy(\$response->result->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()});
//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));
BODY;
        $method->setBody($body);

    }
}
