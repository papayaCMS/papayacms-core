<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Schema\Structure\FieldStructure;
  use Papaya\Database\Schema\Structure\IndexStructure;
  use Papaya\Database\Schema\Structure\IndexFieldStructure;
  use Papaya\Database\Schema\Structure\TableStructure;
  use Papaya\Database\SQLStatement;

  class MySQLSchema extends AbstractSchema {

    /**
     * @return array
     */
    public function getTables() {
      $tables = [];
      if ($result = $this->_connection->execute('SHOW TABLES')) {
        while ($tableName = $result->fetchField()) {
          $tables[] = $tableName;
        }
      }
      return $tables;
    }

    /**
     * @param string $tableName
     * @param string $tablePrefix optional, default value ''
     * @return TableStructure
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $table = new TableStructure($tableName, $tablePrefix !== '');
      $quotedTableName = $this->getQuotedIdentifier($tableName, $tablePrefix);
      if ($result = $this->_connection->execute("SHOW FIELDS FROM $quotedTableName")) {
        while ($row = $result->fetchAssoc()) {
          $table->fields[] = $this->parseFieldData($row);
        }
      }
      if ($result = $this->_connection->execute("SHOW KEYS FROM $quotedTableName")) {
        $indexFields = [];
        while ($row = $result->fetchAssoc()) {
          $keyName = $row['Key_name'];
          if (!isset($table->indices[$keyName])) {
            $table->indices[] = new IndexStructure(
              $keyName,
              (int)$row['Non_unique'] === 0,
              (isset($row['Index_type']) && $row['Index_type'] === 'FULLTEXT') || $row['Comment'] === 'FULLTEXT'
            );
          }
          $indexFields[$keyName][(int)$row['Seq_in_index']] = $row;
        }
        foreach ($table->indices as $index) {
          $fields = $indexFields[$index->name];
          ksort($fields);
          foreach ($fields as $row) {
            $fieldName = $row['Column_name'];
            $size = 0;
            if ($row['Sub_part'] > 0) {
              $size = $row['Sub_part'];
            } elseif (
              !$index->isFullText &&
              $table->fields[$fieldName]->type === FieldStructure::TYPE_TEXT &&
              $table->fields[$fieldName]->size > 255
            ) {
              $size = 255;
            }
            $index->fields[] = new IndexFieldStructure($fieldName, $size);
          }
        }
      }
      return $table;
    }

    /**
     * @param array $row
     * @return FieldStructure
     */
    private function parseFieldData(array $row) {
      $type = $this->parseFieldType($row['Type']);
      $default = NULL;
      if (isset($row['Default'])) {
        if ($type[0] === 'integer') {
          $default = (int)$row['Default'];
        } elseif ($type[0] === 'float') {
          $default = (float)$row['Default'];
        } elseif (empty($row['Default']) && strtolower($row['Null']) === 'yes') {
          $default = NULL;
        } else {
          $default = $row['Default'];
        }
      }
      return new FieldStructure(
        $row['Field'],
        $type[0],
        $type[1],
        strtolower($row['Extra']) === 'auto_increment',
        strtolower($row['Null']) === 'yes',
        isset($default) ? (string)$default : NULL
      );
    }

    /**
     * @param $typeString
     * @return array [$type, $size]
     */
    private function parseFieldType($typeString) {
      $p = strpos($typeString, '(');
      if ($p !== FALSE) {
        $mysqliType = trim(substr($typeString, 0, $p));
        $size = trim(substr($typeString, $p + 1, strpos($typeString, ')') - $p - 1));
      } else {
        $mysqliType = trim($typeString);
        $size = 0;
      }
      switch (strtoupper($mysqliType)) {
      case 'TINYINT':
      case 'SMALLINT':
        $type = FieldStructure::TYPE_INTEGER;
        $size = 2;
        break;
      case 'MEDIUMINT':
      case 'INT':
      case 'INTEGER':
        $type = FieldStructure::TYPE_INTEGER;
        $size = 4;
        break;
      case 'BIGINT':
        $size = 8;
        $type = FieldStructure::TYPE_INTEGER;
        break;
      case 'FLOAT':
      case 'DOUBLE':
      case 'REAL':
      case 'DECIMAL':
      case 'NUMERIC':
        $type = FieldStructure::TYPE_DECIMAL;
        break;
      case 'TINYTEXT':
        $type = FieldStructure::TYPE_TEXT;
        $size = 255;
        break;
      case 'TEXT':
        $type = FieldStructure::TYPE_TEXT;
        $size = 65535;
        break;
      case 'MEDIUMTEXT':
        $type = FieldStructure::TYPE_TEXT;
        $size = 16777215;
        break;
      case 'LONGTEXT':
        $type = FieldStructure::TYPE_TEXT;
        $size = 4294967295;
        break;
      case 'CHAR':
      case 'VARCHAR':
      default :
        $type = FieldStructure::TYPE_TEXT;
        break;
      }
      return [$type, $size];
    }

    /**
     * @param TableStructure $tableStructure
     * @param string $tablePrefix
     * @return boolean
     */
    public function createTable(TableStructure $tableStructure, $tablePrefix = '') {
      if (count($tableStructure->fields) > 0) {
        $sql = '';
        $autoIncrementField = FALSE;
        /** @var FieldStructure $field */
        foreach ($tableStructure->fields as $field) {
          $extra = $this->getFieldExtrasSQL($field, !$autoIncrementField);
          $sql .= '  '.$this->getQuotedIdentifier($field->name).' '.$this->getFieldTypeSQL($field).$extra.",\n";
        }
        if (count($tableStructure->indices) > 0) {
          if ($primary = $tableStructure->indices->getPrimary()) {
            $fieldsString = '(';
            foreach ($primary->fields as $field) {
              if ($field->size > 0) {
                $fieldsString .= sprintf(
                  '%s (%d), ', $this->getQuotedIdentifier($field->name), $field->size
                );
              } else {
                $fieldsString .= $this->getQuotedIdentifier($field->name).', ';
              }
            }
            $sql .= 'PRIMARY KEY '.substr($fieldsString, 0, -2).'), ';
          }
          foreach ($tableStructure->indices as $index) {
            if (!$index->isPrimary) {
              if ($index->isUnique) {
                $sql .= '  UNIQUE ';
              } elseif ($index->isFullText) {
                $sql .= '  FULLTEXT ';
              } else {
                $sql .= ' KEY ';
              }
              $fieldsString = '(';
                foreach ($index->fields as $field) {
                if ($field->size > 0) {
                  $fieldsString .= sprintf(
                    '%s (%d), ', $this->getQuotedIdentifier($field->name), $field->size
                  );
                } else {
                  $fieldsString .= $this->getQuotedIdentifier($field->name).', ';
                }
              }
              $sql .= $this->getQuotedIdentifier($index->name).' '.substr($fieldsString, 0, -2).'), ';
            }
          }
        }
        $sql = 'CREATE TABLE '.$this->getQuotedIdentifier($tableStructure->name, $tablePrefix).
          ' ( '.substr($sql, 0, -2).' ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        return ($this->_connection->execute($sql) !== FALSE);
      }
      return FALSE;
    }

    /**
     * @param FieldStructure $field
     * @return string
     * @access private
     */
    private function getFieldTypeSQL(FieldStructure $field) {
      switch ($field->type) {
      case FieldStructure::TYPE_INTEGER:
        $size = ($field->size > 0) ? (int)$field->size : 1;
        if ($size <= 2) {
          $result = 'TINYINT';
        } elseif ($size <= 4) {
          $result = 'INT';
        } else {
          $result = 'BIGINT';
        }
        break;
      case FieldStructure::TYPE_DECIMAL:
        list($before, $after) = $field->size;
        $result = 'DECIMAL('.$before.','.$after.')';
        break;
      case FieldStructure::TYPE_TEXT:
      default:
        $size = ($field->size > 0) ? (int)$field->size : 1;
        if ($size <= 4) {
          $result = 'CHAR('.$size.')';
        } elseif ($size <= 255) {
          $result = 'VARCHAR('.$size.')';
        } elseif ($size <= 65535) {
          $result = 'TEXT';
        } elseif ($size <= 16777215) {
          $result = 'MEDIUMTEXT';
        } else {
          $result = 'LONGTEXT';
        }
        break;
      }
      return $result;
    }

    /**
     * Get MySQL field extras
     *
     * @param FieldStructure $field
     * @param bool $allowAutoIncrement
     * @return string SQL instruction
     */
    private function getFieldExtrasSQL($field, $allowAutoIncrement = FALSE) {
      $defaultStr = '';
      if ($field->allowsNull) {
        $default = NULL;
        $notNullStr = '';
      } else {
        $default = '';
        $notNullStr = ' NOT NULL';
      }
      if (isset($field->defaultValue)) {
        $default = $field->defaultValue;
      }
      if (isset($default)) {
        switch (strtolower($field->type)) {
        case FieldStructure::TYPE_INTEGER:
          $defaultStr = ' DEFAULT '.((int)$default);
          break;
        case FieldStructure::TYPE_DECIMAL:
          $defaultStr = ' DEFAULT '.((float)$default);
          break;
        case FieldStructure::TYPE_TEXT :
          if ($field->size > 255) {
            $defaultStr = '';
          } else {
            $defaultStr = ' DEFAULT '.$this->_connection->quoteString($default);
          }
          break;
        }
      } else {
        $defaultStr = ' DEFAULT NULL';
      }
      if ($allowAutoIncrement && $field->isAutoIncrement) {
        $autoIncrementString = ' auto_increment';
        $defaultStr = '';
      } else {
        $autoIncrementString = '';
      }
      return $defaultStr.$notNullStr.$autoIncrementString;
    }

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function addField($tableName, FieldStructure $fieldStructure) {
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE %s ADD COLUMN %s %s %s',
        $this->getQuotedIdentifier($tableName),
        $this->getQuotedIdentifier($fieldStructure->name),
        $this->getFieldTypeSQL($fieldStructure),
        $this->getFieldExtrasSQL($fieldStructure)
      );
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function changeField($tableName, FieldStructure $fieldStructure) {
      $allowAutoIncrement = FALSE;
      if ($fieldStructure->isAutoIncrement) {
        $sql = 'SHOW COLUMNS FROM '.$this->getQuotedIdentifier($tableName);
        if ($result = $this->_connection->execute($sql)) {
          $oldAutoIncrementField = NULL;
          $fieldExists = FALSE;
          while ($row = $result->fetchAssoc()) {
            $isAutoIncrement = strtolower($row['Extra']) === 'auto_increment';
            if ($row['Field'] === $fieldStructure->name) {
              $allowAutoIncrement = (trim($row['Key']) !== '');
              if ($isAutoIncrement) {
                unset($oldAutoIncrementField);
                break;
              }
              $fieldExists = TRUE;
            } elseif ($isAutoIncrement) {
              $oldAutoIncrementField = $this->parseFieldData($row);
            }
            if ($fieldExists && isset($oldAutoIncrementField)) {
              break;
            }
          }
          $result->free();
          if (isset($oldAutoIncrementField)) {
            $sql = sprintf(
              /** @lang text */
              'ALTER TABLE %s MODIFY COLUMN %s %s %s',
              $this->getQuotedIdentifier($tableName),
              $this->getQuotedIdentifier($oldAutoIncrementField->name),
              $this->getFieldTypeSQL($oldAutoIncrementField),
              $this->getFieldExtrasSQL($oldAutoIncrementField)
            );
            $this->_connection->execute($sql);
          }
        }
      }
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE %s MODIFY COLUMN %s %s %s',
        $this->getQuotedIdentifier($tableName),
        $this->getQuotedIdentifier($fieldStructure->name),
        $this->getFieldTypeSQL($fieldStructure),
        $this->getFieldExtrasSQL($fieldStructure, $allowAutoIncrement)
      );
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName) {
      $sql = sprintf(
        /** @lang text */
        'ALTER TABLE %s DROP COLUMN %s',
        $this->getQuotedIdentifier($tableName),
        $this->getQuotedIdentifier($fieldName)
      );
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param IndexStructure $indexStructure
     * @return bool
     */
    public function addIndex($tableName, IndexStructure $indexStructure) {
      return $this->changeIndex($tableName, $indexStructure, FALSE);
    }

    /**
     * @param string $tableName
     * @param IndexStructure $indexStructure
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($tableName, IndexStructure $indexStructure, $dropCurrent = TRUE) {
      $fields = '(';
      /** @var IndexFieldStructure $field */
      foreach ($indexStructure->fields as $field) {
        if ($field->size > 0) {
          $fields .= $this->getQuotedIdentifier($field->name).' ('.$field->size.'), ';
        } else {
          $fields .= $this->getQuotedIdentifier($field->name).', ';
        }
      }
      $fields = substr($fields, 0, -2).')';
      $drop = $dropCurrent ? ' DROP INDEX '.$this->getQuotedIdentifier($indexStructure->name).',' : '';
      if ($indexStructure->isPrimary) {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).$drop.' ADD PRIMARY KEY '.$fields;
      } elseif ($indexStructure->isFullText) {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).$drop.
          ' ADD FULLTEXT '.$this->getQuotedIdentifier($indexStructure->name).' '.$fields;
      } elseif ($indexStructure->isUnique) {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).$drop.
          ' ADD UNIQUE '.$this->getQuotedIdentifier($indexStructure->name).' '.$fields;
      } else {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).$drop.
          ' ADD INDEX '.$this->getQuotedIdentifier($indexStructure->name).' '.$fields;
      }
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName) {
      if ($indexName === IndexStructure::PRIMARY) {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).' DROP PRIMARY KEY';
      } else {
        $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).' DROP INDEX '.
          $this->getQuotedIdentifier($indexName);
      }
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param FieldStructure $expectedStructure
     * @param FieldStructure $currentStructure
     * @return bool
     */
    public function isFieldDifferent(FieldStructure $expectedStructure, FieldStructure $currentStructure) {
      if ($expectedStructure->type !== $currentStructure->type) {
        return TRUE;
      }
      if ($expectedStructure->size !== $currentStructure->size) {
        return TRUE;
      }
      if ($expectedStructure->allowsNull !== $currentStructure->allowsNull) {
        return TRUE;
      }
      if ($expectedStructure->isAutoIncrement !== $currentStructure->isAutoIncrement) {
        return TRUE;
      }
      if ($expectedStructure->defaultValue !== $currentStructure->defaultValue) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param IndexStructure $expectedStructure
     * @param IndexStructure $currentStructure
     * @return bool
     */
    public function isIndexDifferent(IndexStructure $expectedStructure, IndexStructure $currentStructure) {
      if (
        ($expectedStructure->isUnique || $expectedStructure->isPrimary()) !==
        ($currentStructure->isUnique || $currentStructure->isPrimary())
      ) {
        return TRUE;
      }
      if ($expectedStructure->isFullText !== $currentStructure->isFullText) {
        return TRUE;
      }
      if (count(array_diff($expectedStructure->fields->keys(), $currentStructure->fields->keys())) !== 0) {
        return TRUE;
      }
      foreach ($expectedStructure->fields as $field) {
        if ($field->size !== $currentStructure->fields[$field->name]->size) {
          return TRUE;
        }
      }
      return FALSE;
    }
  }
}
