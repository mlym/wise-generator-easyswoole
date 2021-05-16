<?php

namespace Mlym\CodeGeneration\JsonGeneration\parser;

use EasySwoole\ORM\Utility\Schema\Column;
use EasySwoole\ORM\Utility\Schema\Table;
use EasySwoole\Spl\SplBean;
use EasySwoole\Spl\SplString;
use Mlym\CodeGeneration\JsonGeneration\bean\ColumnBean;

/**
 * Class SchemaParser
 * @package Mlym\CodeGeneration\JsonGeneration\parser
 */
class SchemaParser implements ParserInterface
{
    /**
     * @var Table 数据库Schema
     */
    protected $schemaInfo;
    /**
     * @var string[] 后缀映射表
     */
    protected $suffixArray = [
        '_time', '_image', '_images', '_file', '_files', '_id', '_ids', '_list', '_data', '_switch'
    ];

    /**
     * @var string[] 前缀映射表
     */
    protected $prefixArray = [
        'is_'
    ];

    /**
     * @var array Option模版
     * TODO 涉及多语言时要进行调整
     */
    protected $optionTemplate = [
        'is_' => [['key' => 0, 'value' => '否'], ['key' => 1, '是']]
    ];


    public function __construct(Table $schemaInfo)
    {
        $this->schemaInfo = $schemaInfo;
    }

    function run(): array
    {
        $columns = [];
        foreach ($this->schemaInfo->getColumns() as $column) {
            $columnBean = new ColumnBean();
            $columnBean->setName($column->getColumnName());
            $columnBean->setDescription($column->getColumnComment());
            $this->parseType($column, $columnBean);
            $this->parseValidate($column, $columnBean);
            $columns[] = $columnBean->toArray(null, SplBean::FILTER_NOT_NULL);
        }
        return $columns;
    }

    /**
     * 解析数据类型，配置输出类型、选项值、多选项等
     * @param Column $column
     * @param ColumnBean $columnBean
     */
    private function parseType(Column $column, ColumnBean $columnBean)
    {
        $columnBean->setType('string');
        $columnNamePrefix = $this->parsePrefix($column->getColumnName());
        $columnNameSuffix = $this->parseSuffix($column->getColumnName());
        if (in_array($columnNamePrefix, $this->prefixArray)) {
            /**
             * 根据前缀转换类型
             */
            switch ($columnNamePrefix) {
                case 'is_':
                    $columnBean->setType('radio');
                    $columnBean->setOption($this->optionTemplate[$columnNamePrefix]);
                    break;
            }
        } else if (in_array($columnNameSuffix, $this->suffixArray)) {
            /**
             * 根据后缀转换类型
             */
            switch ($columnNameSuffix) {
                case '_id':
                    if ($column->getColumnType() == 'int' || $column->getColumnType() == 'varchar')
                        $columnBean->setType('select');
                    break;
                case '_ids':
                    if ($column->getColumnType() == 'varchar')
                        $columnBean->setType('select');
                    break;
                case '_list':
                    if ($column->getColumnType() == 'enum') {
                        $columnBean->setType('select');
                        $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    } else if ($column->getColumnType() == 'set') {
                        $columnBean->setType('select');
                        $columnBean->setMultiple(true);
                        $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    }
                    break;
                case '_data':
                    if ($column->getColumnType() == 'enum') {
                        $columnBean->setType('radio');
                        $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    } else if ($column->getColumnType() == 'set') {
                        $columnBean->setType('checkbox');
                        $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    }
                    break;
                case '_switch':
                    if ($column->getColumnType() == 'tinyint')
                        $columnBean->setType('switch');
                    break;
                case '_time':
                    if ($column->getColumnType() == 'int' || $column->getColumnType() == 'datetime')
                        $columnBean->setType('datetime');
                    break;
                case '_date':
                    if ($column->getColumnType() == 'date')
                        $columnBean->setType('date');
                    break;
                case '_image':
                    if ($column->getColumnType() == 'varchar')
                        $columnBean->setType('image');
                    break;
                case '_images':
                    if ($column->getColumnType() == 'varchar')
                        $columnBean->setType('image');
                    $columnBean->setMultiple(true);
                    break;
                case '_file':
                    if ($column->getColumnType() == 'int' || $column->getColumnType() == 'datetime')
                        $columnBean->setType('file');
                    break;
                case '_files':
                    if ($column->getColumnType() == 'date')
                        $columnBean->setType('file');
                    $columnBean->setMultiple(true);
                    break;
            }
        } else {
            /**
             * 根据数据类型转换类型
             */
            switch ($column->getColumnType()) {
                case 'int':
                    $columnBean->setType('int');
                    break;
                case 'char':
                case 'varchar':
                    $columnBean->setType('string');
                    break;
                case 'enum':
                    $columnBean->setType('select');
                    $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    break;
                case 'set':
                    $columnBean->setType('select');
                    $columnBean->setMultiple(true);
                    $columnBean->setOption($this->parseOption($column->getColumnLimit()));
                    break;
                case 'text':
                    $columnBean->setType('textarea');
                    break;
                case 'datetime':
                case 'timestamp':
                    $columnBean->setType('datetime');
                    break;
                case 'date':
                    $columnBean->setType('date');
            }
        }
    }

    /**
     * 解析验证规则
     * @param Column $column
     * @param ColumnBean $columnBean
     */
    private function parseValidate(Column $column, ColumnBean $columnBean)
    {
        $validate = [];
        /**
         * require
         */
        if ($column->getIsNotNull()) {
            $validate[] = [
                'required' => true,
                'trigger' => $this->parseTrigger($columnBean->getType())
            ];
        }

        empty($validate) || $columnBean->setValidate($validate);
    }

    /**
     * 解析字段名后缀
     * @param string $columnName
     * @return mixed|string
     */
    private function parseSuffix(string $columnName)
    {
        if (preg_match("/_[a-zA-Z]+$/", $columnName, $match)) {
            return $match[0];
        }
        return '';
    }

    /**
     * 解析字段名前缀
     * @param string $columnName
     * @return mixed|string
     */
    private function parsePrefix(string $columnName)
    {
        if (preg_match("/^[a-zA-z]+_/", $columnName, $match)) {
            return $match[0];
        }
        return '';
    }

    /**
     * 解析ColumnLimit转换为Option
     * @param array $columnLimit
     * @return array
     */
    private function parseOption(array $columnLimit)
    {
        $option = [];
        foreach ($columnLimit as $index => $item) {
            $option[] = [
                'key' => $index + 1,
                'value' => str_replace("'", '', $item)
            ];
        }
        return $option;
    }

    /**
     * 根据输出的字段类型解析验证规则的触发条件
     * @param string $columnType
     * @return string
     */
    private function parseTrigger(string $columnType)
    {
        $trigger = 'blur';
        switch ($columnType) {
            case 'int':
            case 'string':
            case 'textarea':
                $trigger = 'blur';
                break;
            case 'select':
            case 'checkbox':
            case 'radio':
                $trigger = 'change';
                break;
        }
        return $trigger;
    }


}