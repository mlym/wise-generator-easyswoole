<?php
namespace Mlym\CodeGeneration\UnitTest\Method;


use Mlym\CodeGeneration\Unity\Unity;

class Del extends UnitTestMethod
{
    protected $methodName = 'testDel';
    protected $actionName = 'delete';
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

\$delData = [];
\$delData['{$this->classGeneration->getConfig()->getTable()->getPkFiledName()}'] = \$model->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()};
\$response = \$this->request('{$this->actionName}',\$delData);
//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));

BODY;
        $method->setBody($body);

    }
}
