<?php

namespace Mlym\CodeGeneration\Unity;

use EasySwoole\ORM\Db\ClientInterface;
use EasySwoole\ORM\Db\ConnectionInterface;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\ORM\Utility\TableObjectGeneration;
use EasySwoole\ORM\Utility\Schema\Table;

class TableObjectGenerationSub extends TableObjectGeneration
{
    public function __construct(ConnectionInterface $connection, $tableName, ?ClientInterface $client = null)
    {
        parent::__construct($connection, $tableName, $client);
    }

    /**
     * @return Table
     * @throws Exception
     */
    public function generationTable()
    {
        $this->getTableColumnsInfo();
        $columns = $this->tableColumns;
        $table = new Table($this->tableName);
        foreach ($columns as $column) {
            //新增字段对象
            $columnObj = $this->getTableColumn($column);
            $columnObj->setIsNotNull(strtoupper($column['Null'] ?? '') == 'NO');
            $table->addColumn($columnObj);
        }
        return $table;
    }
}