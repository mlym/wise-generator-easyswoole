<?php
namespace Mlym\CodeGeneration\UnitTest\Method;


use Mlym\CodeGeneration\Unity\Unity;

class Update extends UnitTestMethod
{
    protected $methodName = 'testUpdate';
    protected $actionName = 'update';
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

\$update = [];
\$update['{$this->classGeneration->getConfig()->getTable()->getPkFiledName()}'] = \$model->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()};

BODY;

        $body .= $this->getTableTestData('update');

        $body .= <<<BODY
\$response = \$this->request('{$this->actionName}',\$update);
\$model->destroy(\$model->{$this->classGeneration->getConfig()->getTable()->getPkFiledName()});
//var_dump(json_encode(\$response,JSON_UNESCAPED_UNICODE));
BODY;
        $method->setBody($body);

    }
}
