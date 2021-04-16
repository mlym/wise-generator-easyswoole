<?php
namespace Mlym\CodeGeneration\ClassGeneration;


use EasySwoole\Utility\File;
use Nette\PhpGenerator\PhpNamespace;

class ClassGeneration
{
    /**
     * @var $config Config;
     */
    protected $config;
    protected $phpClass;
    protected $phpNamespace;
    protected $methodGenerationList = [];

    /**
     * BeanBuilder constructor.
     * @param        $config
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        File::createDirectory($config->getDirectory());
        $phpNamespace = new PhpNamespace($config->getNamespace());

        $this->config = $config;
        $this->phpNamespace = $phpNamespace;
        $this->phpClass = $phpNamespace->addClass($this->getClassName());
        $this->addExtend();
    }

    /**
     * 添加继承类
     */
    protected function addExtend()
    {
        $extendClass = $this->config->getExtendClass();
        if (!empty($extendClass)) {
            $this->phpNamespace->addUse($this->config->getExtendClass());
            $this->phpClass->addExtend($this->config->getExtendClass());
        }
    }

    final function generate()
    {
        $this->addComment();
        $this->addClassData();
        /**
         * @var $method MethodAbstract
         */
        foreach ($this->methodGenerationList as $method) {
            $method->run();
        }
        return $this->createPHPDocument();
    }

    function addClassData()
    {

    }

    /**
     * 添加注解
     */
    protected function addComment()
    {
        $this->phpClass->addComment("{$this->getClassName()}");
        $this->phpClass->addComment("Class {$this->getClassName()}");
    }

    protected function getClassName()
    {
        return $this->config->getClassName();
    }


    protected function createPHPDocument()
    {
        $fileName = $this->config->getDirectory() . '/' . $this->getClassName();
        $content = "<?php\n\n{$this->phpNamespace}\n";
        $result = File::createFile($fileName . '.php', $content);
        return $result == false ? $result : $fileName . '.php';
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return \Nette\PhpGenerator\ClassType
     */
    public function getPhpClass(): \Nette\PhpGenerator\ClassType
    {
        return $this->phpClass;
    }

    /**
     * @return PhpNamespace
     */
    public function getPhpNamespace(): PhpNamespace
    {
        return $this->phpNamespace;
    }

    public function addGenerationMethod(MethodAbstract $abstract)
    {
        $this->methodGenerationList[$abstract->getMethodName()] = $abstract;
    }


}
