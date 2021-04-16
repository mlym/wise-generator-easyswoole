<?php
namespace Mlym\CodeGeneration\ClassGeneration;


abstract class MethodAbstract
{
    /**
     * @var ClassGeneration
     */
    protected $classGeneration;
    /**
     * @var \Nette\PhpGenerator\Method
     */
    protected $method;

    function __construct(ClassGeneration $classGeneration)
    {
        $this->classGeneration = $classGeneration;
        $method = $classGeneration->getPhpClass()->addMethod($this->getMethodName());
        $this->method = $method;
    }

    function run()
    {
        $this->addComment();
        $this->addMethodBody();
    }

    function addComment()
    {
        return;
    }

    abstract function addMethodBody();

    abstract function getMethodName(): string;
}
