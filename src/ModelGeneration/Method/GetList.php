<?php
namespace Mlym\CodeGeneration\ModelGeneration\Method;




use Mlym\CodeGeneration\ClassGeneration\MethodAbstract;

class GetList extends MethodAbstract
{
    function addMethodBody()
    {
        $method = $this->method;

        //配置返回类型
        $method->setReturnType('array');

        //配置方法参数
        $method->addParameter('pageNo', 1)
            ->setType('int');
        $method->addParameter('pageSize', 10)
            ->setType('int');
        $method->addParameter('field', '*')->setType('string');

        $methodBody = '';
        $methodBody .= <<<Body
        
\$list = \$this
    ->withTotalCount()
	->order(\$this->schemaInfo()->getPkFiledName(), 'DESC')
    ->field(\$field)
    ->page(\$pageNo, \$pageSize)
    ->all();
\$total = \$this->lastQueryResult()->getTotalCount();
\$data = [
    'pageNo' => \$pageNo,
    'pageSize' => \$pageSize,
    'list' => \$list,
    'total' => \$total,
    'pageCount' => ceil(\$total / \$pageSize)
];
return \$data;
Body;
        //配置方法内容
        $method->setBody($methodBody);
    }

    function getMethodName(): string
    {
        return "getList";
    }

    function addComment()
    {
        $comment = <<<COMMENT
@param int \$pageNo 页码，默认1
@param int \$pageSize 每页数量，默认10
@param string \$field 显示字段，默认*
@return array
@throws \EasySwoole\ORM\Exception\Exception
@throws \Throwable
COMMENT;
        $this->method->addComment($comment);

    }
}
