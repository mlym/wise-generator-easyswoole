<?php
namespace Mlym\CodeGeneration\JsonGeneration\bean;
use EasySwoole\Spl\SplBean;

/**
 * 用于输出Json的字段Bean（非数据库字段）
 * Class ColumnBean
 * @package Mlym\CodeGeneration\JsonGeneration\bean
 */
class ColumnBean extends SplBean
{
    /**
     * @var string 字段名称
     */
    protected $name;
    /**
     * @var string 字段描述
     */
    protected $description;
    /**
     * @var string 字段类型
     */
    protected $type;
    /**
     * @var array 验证规则
     */
    protected $validate;
    /**
     * @var bool 多选，适用于下拉框、上传等
     */
    protected $multiple;

    /**
     * @var array 选项值，当type为select、radio、checkbox时该字段值存在
     */
    protected $option;

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param array $validate
     */
    public function setValidate(array $validate): void
    {
        $this->validate = $validate;
    }

    /**
     * @param bool $multiple
     */
    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    /**
     * @param array $option
     */
    public function setOption(array $option): void
    {
        $this->option = $option;
    }


}