<?php

namespace Mlym\CodeGeneration\JsonGeneration;

use EasySwoole\ORM\Db\ConnectionInterface;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\ORM\Utility\Schema\Table;
use Mlym\CodeGeneration\JsonGeneration\parser\SchemaParser;
use Mlym\CodeGeneration\Unity\TableObjectGenerationSub;

class JsonGeneration
{
    /**
     * @var string 表名
     */
    protected $tableName;
    /**
     * @var Table 数据表Schema信息
     */
    protected $schemaInfo;
    /**
     * @var ConnectionInterface 数据库连接
     */
    protected $connection;
    /**
     * @var array 配置项
     */
    protected $config;
    /**
     * @var array 输出对象
     */
    protected $output = [];

    /**
     * JsonGeneration constructor.
     * @param string $tableName 表名称
     * @param ConnectionInterface $connection 数据库链接
     * @param array $config 配置项
     * @throws \Throwable
     */
    function __construct(string $tableName, ConnectionInterface $connection, array $config)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->config = $config;
    }

    /**
     * 构建Json中间件
     * @throws Exception
     */
    public function build()
    {
        /**
         * Schema解析
         */
        $tableObjectGeneration = new TableObjectGenerationSub($this->connection, $this->tableName);
        $schemaInfo = $tableObjectGeneration->generationTable();
        $schemaParser = new SchemaParser($schemaInfo);
        $schemaOutput = $schemaParser->run();

        /**
         * Action解析
         * 当前版本固定不变，保留位置后续版本扩展
         */
        $actionOutput = [
            "add" => true,
            "edit" => true,
            "del" => true,
            "detail" => true,
            "import" => true,
            "export" => true
        ];

        /**
         * output
         */
        $this->output = [
            'schema' => $schemaOutput,
            'action' => $actionOutput
        ];
    }

    /**
     * @return array 输出数组
     */
    public function toArray()
    {
        return $this->output;
    }

    /**
     * @return false|string 输出Json格式
     */
    public function toJson()
    {
        return json_encode($this->output, 320);
    }

    /**
     * @return string 输出JSON文件
     */
    public function toFile()
    {
        $path = EASYSWOOLE_ROOT . '/json_file/';
        $fileName = $this->tableName . '.json';
        $filePath = $path . $fileName;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents($filePath, $this->toJson());
        return $filePath;
    }
}