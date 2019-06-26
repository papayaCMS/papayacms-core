<?php

namespace Papaya\Database {

  interface Schema {

    /**
     * @return array
     */
    public function getTables();

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return array
     */
    public function describeTable($tableName, $tablePrefix = '');

    /**
     * @param array $tableStructure
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable(array $tableStructure, $tablePrefix = '');

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function addField($tableName, array $fieldStructure);

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function changeField($tableName, array $fieldStructure);

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName);

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @return bool
     */
    public function addIndex($tableName, array $indexStructure);

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($tableName, array $indexStructure, $dropCurrent = TRUE);

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName);

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     */
    public function isFieldDifferent(array $expectedStructure, array $currentStructure);

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     */
    public function isIndexDifferent(array $expectedStructure, array $currentStructure);
  }

}
