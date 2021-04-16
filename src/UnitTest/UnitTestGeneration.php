<?php
namespace Mlym\CodeGeneration\UnitTest;

use Mlym\CodeGeneration\ClassGeneration\ClassGeneration;
use Mlym\CodeGeneration\UnitTest\Method\Add;
use Mlym\CodeGeneration\UnitTest\Method\Del;
use Mlym\CodeGeneration\UnitTest\Method\GetList;
use Mlym\CodeGeneration\UnitTest\Method\GetOne;
use Mlym\CodeGeneration\UnitTest\Method\Update;
use Mlym\CodeGeneration\Unity\Unity;
use Nette\PhpGenerator\ClassType;

class UnitTestGeneration extends ClassGeneration
{
    /**
     * @var $config UnitTestConfig
     */
    protected $config;

    function addClassData()
    {
        $this->phpClass->addProperty('modelName', $this->getApiUrl());
        $this->phpNamespace->addUse($this->config->getModelClass());
        $this->addGenerationMethod(new Add($this));
        $this->addGenerationMethod(new GetOne($this));
        $this->addGenerationMethod(new Update($this));
        $this->addGenerationMethod(new GetList($this));
        $this->addGenerationMethod(new Del($this));
    }

    protected function getApiUrl()
    {
        $baseNamespace = $this->config->getControllerClass();
        $modelName = str_replace(['App\\HttpController', '\\'], ['', '/'], $baseNamespace);
        return $modelName;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        $className = $this->config->getRealTableName() . $this->config->getFileSuffix();
        return $className;
    }

}
