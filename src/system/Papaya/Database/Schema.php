<?php

namespace Papaya\Database {

  use Papaya\Database\Schema\Structure\FieldStructure;
  use Papaya\Database\Schema\Structure\IndexStructure;
  use Papaya\Database\Schema\Structure\TableStructure;

  interface Schema {

    /**
     * @return array
     */
    public function getTables();

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return TableStructure
     */
    public function describeTable($tableName, $tablePrefix = '');

    /**
     * @param TableStructure $tableStructure
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable(TableStructure $tableStructure, $tablePrefix = '');

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function addField($tableName, FieldStructure $fieldStructure);

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function changeField($tableName, FieldStructure $fieldStructure);

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName);

    /**
     * @param string $tableName
     * @param IndexStructure $indexStructure
     * @return bool
     */
    public function addIndex($tableName, IndexStructure $indexStructure);

    /**
     * @param string $tableName
     * @param IndexStructure $indexStructure
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($tableName, IndexStructure $indexStructure, $dropCurrent = TRUE);

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName);

    /**
     * @param FieldStructure $expectedStructure
     * @param FieldStructure $currentStructure
     * @return bool
     */
    public function isFieldDifferent(FieldStructure $expectedStructure, FieldStructure $currentStructure);

    /**
     * @param IndexStructure $expectedStructure
     * @param IndexStructure $currentStructure
     * @return bool
     */
    public function isIndexDifferent(IndexStructure $expectedStructure, IndexStructure $currentStructure);
  }

}
