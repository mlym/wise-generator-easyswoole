<?php

namespace Mlym\CodeGeneration\ModelGeneration\Method;

use EasySwoole\Utility\Str;
use Mlym\CodeGeneration\ClassGeneration\ClassGeneration;
use Mlym\CodeGeneration\ClassGeneration\MethodAbstract;

class Setter extends MethodAbstract
{
    private $methodName;
    private $comment;

    public function __construct(ClassGeneration $classGeneration, String $methodName)
    {
        $this->setMethodName($methodName);
        parent::__construct($classGeneration);
    }

    function addMethodBody()
    {
        $method = $this->method;

        //配置返回类型
        $method->setReturnType('int');
        //配置方法参数
        $method->addParameter('value');
        $method->addParameter('data')
            ->setType('array');
        $methodBody = '';
        $methodBody .= <<<Body
return is_numeric(\$value) ? \$value : \$value == 'true' || \$value == true ? 1 : 0;
Body;
        //配置方法内容
        $method->setBody($methodBody);
    }

    /**
     * 添加注释
     */
    function addComment()
    {
        $comment = <<<COMMENT
{$this->getComment()}
@param \$value mixed 原值
@param array \$data 当前对象值
@return int
COMMENT;
        $this->method->setComment($comment);
    }

    /**
     * @return mixed
     */
    public function getMethodName(): string
    {
        return 'set' . Str::studly($this->methodName) . 'Attr';
    }

    /**
     * @param mixed $methodName
     */
    public function setMethodName($methodName): void
    {
        $this->methodName = $methodName;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment): void
    {
        $this->comment = $comment;
    }

}
