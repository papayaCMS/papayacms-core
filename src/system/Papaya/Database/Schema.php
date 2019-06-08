<?php

namespace Papaya\Database {

  interface Schema {

    /**
     * @return array
     */
    public function getTables();

    /**
     * @param array $tableData
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable($tableData, $tablePrefix);

    /**
     * @param string $table
     * @param array $fieldData
     * @return bool
     */
    public function addField($table, $fieldData);

    /**
     * @param string $table
     * @param array $fieldData
     * @return bool
     */
    public function changeField($table, $fieldData);

    /**
     * @param string $table
     * @param string $field
     * @return bool
     */
    public function dropField($table, $field);

    /**
     * @param string $table
     * @param array $index
     * @return bool
     */
    public function addIndex($table, $index);

    /**
     * @param string $table
     * @param array $index
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($table, $index, $dropCurrent = TRUE);

    /**
     * @param string $table
     * @param string $name
     * @return bool
     */
    public function dropIndex($table, $name);

  }

}
