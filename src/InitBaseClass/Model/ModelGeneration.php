<?php
namespace Mlym\CodeGeneration\InitBaseClass\Model;


use Mlym\CodeGeneration\ClassGeneration\ClassGeneration;
use Mlym\CodeGeneration\InitBaseClass\Model\ModelConfig;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\DbManager;

class ModelGeneration extends ClassGeneration
{
    /**
     * @var $config ModelConfig
     */
    protected $config;
    public function __construct(?ModelConfig $config=null)
    {
        if (empty($config)){
            $config = new ModelConfig("BaseModel","App\\Model");
            $config->setExtendClass(AbstractModel::class);
        }
        parent::__construct($config);
    }

    function addClassData()
    {
        $this->phpNamespace->addUse(DbManager::class);
        $method = $this->phpClass->addMethod('transaction');
        $method->addComment(<<<COMMENT
Transaction
@param callable \$callable
@return mixed
@throws \Throwable
COMMENT);
        $method->setStatic();
        $method->addParameter('callable')->setType('callable');
        $method->setBody(<<<BODY
try {
    DbManager::getInstance()->startTransaction();
    \$result = \$callable();
    DbManager::getInstance()->commit();
    return \$result;
} catch (\Throwable \$throwable) {
    DbManager::getInstance()->rollback();
    throw \$throwable;
}
BODY
        );
        $method = $this->phpClass->addMethod('getPageList');
        $method->addComment(<<<COMMENT
GetList
@param int \$pageNo
@param int \$pageSize
@param bool \$isCount
@return array
@throws \EasySwoole\ORM\Exception\Exception
@throws \Throwable
COMMENT);
        $method->addParameter('pageNo')->setType('int')->setDefaultValue(1);
        $method->addParameter('pageSize')->setType('int')->setDefaultValue(20);
        $method->addParameter('isCount')->setType('bool')->setDefaultValue(true);
        $method->setBody(<<<BODY
if (\$isCount){
    \$this->withTotalCount();
}
\$list = \$this
    ->page(\$pageNo, \$pageSize)
    ->all();
\$data = [];
\$data['list'] = \$list;
\$data['pageNo'] = \$pageNo;
\$data['pageSize']  = \$pageSize;
if (\$isCount){
    \$total = \$this->lastQueryResult()->getTotalCount();
    \$data['total'] = \$total;
    \$data['pageCount'] = ceil(\$total / \$pageSize);
}

return \$data;
BODY
        );
    }
}
