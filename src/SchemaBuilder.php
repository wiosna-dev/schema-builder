<?php

namespace SchemaBuilder\SchemaBuilder;

use Exception;

class SchemaBuilder
{
    protected $prefix;
    protected $namespace;
    protected $columns = [];

    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function addColumn($columnData)
    {
        $this->columns[] = $columnData;
        return $this;
    }

    public function buildSql($tableName)
    {
        if (empty($tableName) or empty($this->namespace)) {
            throw new Exception('"tableName" or "namespace" empty.');
        }

        $sql = '';
        $sql .= 'CREATE TABLE IF NOT EXIST `'.$this->buildTableName().'` (';

        foreach ($this->columns as &$column) {
            $sql .= $this->buildColumn($column);
        }
        $sql = substr($sql, 0, -1);

        $sql .= "\n".') ENGINE = InnoDB;';

        return $sql;
    }

    private function buildTableName()
    {
        $sql = '';
        if (!empty($this->prefix) {
            $sql .= $this->prefix;
        }
        $sql .= $this->namespace.'_$_'.$tableName;

        return $sql;
    }

    private function buildColumn(&$column)
    {
        $sql = '';

        $sql .= "\n\t".'`'.$column['name'].'` '.$column['type'];

        if (isset($column['length'])) {
            $sql .= '('.$column['length'].') ';
        } else {
            $sql .= ' ';
        }

        if (isset($column['not_null']) and $column['not_null']===true) {
            $sql .= 'NOT NULL ';
        }

        if (isset($column['auto_increment']) and $column['auto_increment']===true) {
            $sql .= 'AUTO_INCREMENT ';
        }

        if (isset($column['default'])) {
            $sql .= 'DEFAULT '.$column['default'].' ';
        }

        if (isset($column['on_update'])) {
            $sql .= 'ON UPDATE '.$column['on_update'].' ';
        }

        $sql = substr($sql, 0, -1).',';

        if (isset($column['primary_key']) and $column['primary_key']===true) {
            $sql .= "\n\t".'PRIMARY KEY (`'.$column['name'].'`),';
        }

        if (isset($column['foreign_key_table'])) {
            $sql .= "\n\t".'FOREIGN KEY (`'.$column['name'].'`) ';
            $sql .= 'REFERENCES '.str_replace('{prefix}', $this->prefix, $column['foreign_key_table']).'(`'.$column['foreign_key_column'].'`),';
        }

        return $sql;
    }
}