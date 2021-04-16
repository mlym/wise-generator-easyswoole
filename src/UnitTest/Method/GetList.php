<?php
namespace Mlym\CodeGeneration\UnitTest\Method;


use Mlym\CodeGeneration\Unity\Unity;

class GetList extends UnitTestMethod
{
    protected $methodName = 'testGetList';
    protected $actionName = 'getList';
    function addMethodBody()
    {
        $method = $this->method;
        $modelName = Unity::getModelName($this->classGeneration->getConfig()->getModelClass());
        $body = <<<BODY
\$model = new {$modelName}();
\$data = [];
\$response = \$this->request('{$this->actionName}',\$data);

//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));

BODY;
        $method->setBody($body);
    }
}
