<?php

namespace Mlym\CodeGeneration\ModelGeneration;

use EasySwoole\Utility\Str;
use Mlym\CodeGeneration\ClassGeneration\ClassGeneration;
use Mlym\CodeGeneration\ModelGeneration\Method\GetList;
use Mlym\CodeGeneration\ModelGeneration\Method\Setter;
use Mlym\CodeGeneration\Unity\Unity;
use Nette\PhpGenerator\ClassType;

class ModelGeneration extends ClassGeneration
{
    /**
     * @var $config ModelConfig
     */
    protected $config;

    function addClassData()
    {
        $this->addClassBaseContent();
        $this->addGenerationMethod(new GetList($this));

        /**
         * 设置器
         * 作用：在前端传bool参数时，model层无法处理，会认为空（具体原因还在查），通过setter的方式，将bool转成0和1
         */
        foreach ($this->config->getTable()->getColumns() as $field) {
            /**
             * 设置"是否"选项时的条件：数据库配置类型为tinyint、以is字符开头
             */
            if ($field->getColumnType() == 'tinyint' && Str::startsWith($field->getColumnName(),'is',false)) {
                $setterObj = new Setter($this,$field->getColumnName());
                $setterObj->setComment($field->getColumnComment());
                $this->addGenerationMethod($setterObj);
            }
        }
    }


    protected function addClassBaseContent(): ClassType
    {
        $createTimeArray = ['create_time', 'createTime', 'createAt'];
        $updateTimeArray = ['update_time', 'updateTime', 'updateAt'];
        $table = $this->config->getTable();
        $phpClass = $this->phpClass;
        //配置表名属性
        $phpClass->addProperty('tableName', $table->getTable())
            ->setVisibility('protected');
        //字段名称列表
        $columnList = array_keys($table->getColumns());
        //定义创建时间字段
        $createTimeField = array_intersect($createTimeArray, $columnList);
        if (!empty($createTimeField)) {
            $phpClass->addProperty('createTime', $createTimeField[0])
                ->setVisibility('protected');
        }
        //定义更新时间字段
        $updateTimeField = array_intersect($updateTimeArray, $columnList);
        if (!empty($updateTimeField)) {
            $phpClass->addProperty('updateTime', $updateTimeField[0])
                ->setVisibility('protected');
        }
        //定义自动更新时间
        if ($createTimeField || $updateTimeField) {
            $phpClass->addProperty('autoTimeStamp', true)
                ->setVisibility('protected');
        }
        //填写基础属性注释
        foreach ($table->getColumns() as $column) {
            $name = $column->getColumnName();
            $comment = $column->getColumnComment();
            $columnType = Unity::convertDbTypeToDocType($column->getColumnType());
            $phpClass->addComment("@property {$columnType} \${$name} // {$comment}");
        }
        return $phpClass;
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
