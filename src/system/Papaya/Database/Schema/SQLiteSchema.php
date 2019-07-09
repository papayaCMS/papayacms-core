<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Record\Order\Field;
  use Papaya\Database\Schema\Structure\FieldStructure;
  use Papaya\Database\Schema\Structure\IndexFieldStructure;
  use Papaya\Database\Schema\Structure\IndexStructure;
  use Papaya\Database\Schema\Structure\TableStructure;
  use Papaya\Database\SQLStatement;

  class SQLiteSchema extends AbstractSchema {
    const ACTION_CHANGE_FIELD = 'change';
    const ACTION_DROP_FIELD = 'drop';

    /**
     * @return array
     */
    public function getTables() {
      return array_map(
        static function ($row) {
          return $row['name'];
        },
        iterator_to_array(
          $this->_connection->execute(
            "SELECT name FROM sqlite_master WHERE type = 'table'"
          )
        )
      );
    }

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function addField($tableName, FieldStructure $fieldStructure) {
      return $this->changeField($tableName, $fieldStructure);
    }

    /**
     * @param string $tableName
     * @param FieldStructure $fieldStructure
     * @return bool
     */
    public function changeField($tableName, FieldStructure $fieldStructure) {
      return $this->alterTable($tableName, self::ACTION_CHANGE_FIELD, $fieldStructure);
    }

    /**
     * Change table structure
     *
     * @param string $tableName
     * @param string $action
     * @param FieldStructure|string $field
     * @return boolean
     */
    private function alterTable($tableName, $action, $field) {
      // get current table definitions
      $tableStructure = $this->describeTable($tableName);
      // create temporary table with old table definitions
      $temporaryTableName = 'tmp_'.$tableName;
      $temporaryTableStructure = clone $tableStructure;
      $temporaryTableStructure->name = $temporaryTableName;
      $this->_connection->execute(
        sprintf('DROP TABLE IF EXISTS "%s"', $temporaryTableName)
      );
      $this->createTable($temporaryTableStructure);
      // copy data from old table to temporary table (insert select)
      $this->_connection->execute(
        sprintf(
          'INSERT INTO %1$s (%2$s) SELECT %2$s FROM %3$s',
          $this->getQuotedIdentifier($temporaryTableName),
          implode(',', $this->getQuotedIdentifiers($tableStructure->fields->keys())),
          $this->getQuotedIdentifier($tableName)
        )
      );
      // drop old table
      $this->_connection->execute(sprintf('DROP TABLE %s', $this->getQuotedIdentifier($tableName)));

      // apply action to table definition
      switch ($action) {
      case self::ACTION_DROP_FIELD:
        $tableStructure->fields->remove($field);
        break;
      case self::ACTION_CHANGE_FIELD:
        $tableStructure->fields[$field->name] = $field;
        break;
      }

      // create new table with new table definitions
      $this->createTable($tableStructure);
      // copy data from temporary table to new table (insert select)
      $fieldNames = array_intersect($tableStructure->fields->keys(), $temporaryTableStructure->fields->keys());
      $result = (bool)$this->_connection->execute(
        sprintf(
          'INSERT INTO %1$s (%2$s) SELECT %2$s FROM %3$s',
          $this->getQuotedIdentifier($tableName),
          implode(',', $this->getQuotedIdentifiers($fieldNames)),
          $this->getQuotedIdentifier($temporaryTableName)
        )
      );
      // drop temporary table
      $this->_connection->execute(sprintf('DROP TABLE %s', $this->getQuotedIdentifier($temporaryTableName)));
      return $result;
    }

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return TableStructure
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $table = new TableStructure($tableName, $tablePrefix !== '');
      $prefixedTableName = $this->getIdentifier($tableName, $tablePrefix);
      $quotedTableName = $this->getQuotedIdentifier($tableName, $tablePrefix);

      if ($dbResult = $this->_connection->execute('PRAGMA table_info('.$quotedTableName.');')) {
        while ($row = $dbResult->fetchAssoc()) {
          $table->fields[] = $this->parseFieldData($row);
          if ($row['pk'] > 0) {
            $primaryIndex = $table->indizes->getPrimary();
            if (!$primaryIndex) {
              $table->indizes[] = $primaryIndex = new IndexStructure(IndexStructure::PRIMARY, TRUE);
            }
            $primaryIndex->fields[] = new IndexFieldStructure($row['name']);
          }
        }
      }
      if ($dbResult = $this->_connection->execute('PRAGMA index_list('.$quotedTableName.')')) {
        $internalKeyNames = [];
        while ($row = $dbResult->fetchAssoc()) {
          if ($row['origin'] === 'pk' || $row['origin'] === 'u') {
            continue;
          }
          if (strpos($row['name'], $prefixedTableName) === 0) {
            $keyName = substr($row['name'], strlen($prefixedTableName) + 1);
          } else {
            $keyName = $row['name'];
          }
          $internalKeyNames[$keyName] = $row['name'];
          $table->indizes[] = new IndexStructure(
            $keyName, (string)$row['unique'] === '1', false
          );
        }
        foreach ($internalKeyNames as $keyName => $internalKeyName) {
          $sql = 'PRAGMA index_info('.$this->getQuotedIdentifier($internalKeyName).')';
          if ($dbResult = $this->_connection->execute($sql)) {
            while ($row = $dbResult->fetchAssoc()) {
              $table->indizes[$keyName]->fields[] = new IndexFieldStructure($row['name']);
            }
          }
        }
      }
      return $table;
    }

    /**
     * Parse SQLite field data
     *
     * @param array $row
     * @return FieldStructure
     */
    private function parseFieldData($row) {
      $autoIncrement = FALSE;
      $default = NULL;
      switch ($row['type']) {
      case 'BIGSERIAL':
      case 'BIGINT':
        $type = FieldStructure::TYPE_INTEGER;
        $size = 8;
        break;
      case 'SERIAL':
      case 'INT':
      case 'INTEGER':
        $type = FieldStructure::TYPE_INTEGER;
        $size = 4;
        if ((int)$row['pk'] === 1) {
          $autoIncrement = TRUE;
        }
        break;
      case 'SMALLINT':
        $type = FieldStructure::TYPE_INTEGER;
        $size = 2;
        break;
      case 'TEXT':
        $type = FieldStructure::TYPE_TEXT;
        $size = 65535;
        break;
      default:
        if (preg_match('(NUMERIC\((\d+,\d+)\))', $row['type'], $regs)) {
          $type = FieldStructure::TYPE_DECIMAL;
          $size = $regs[1];
        } elseif (preg_match('(VARCHAR\((\d+)\))', $row['type'], $regs)) {
          $type = FieldStructure::TYPE_TEXT;
          $size = (int)$regs[1];
        } elseif (preg_match('(CHAR\((\d+)\))', $row['type'], $regs)) {
          $type = FieldStructure::TYPE_TEXT;
          $size = (int)$regs[1];
        } else {
          $type = FieldStructure::TYPE_TEXT;
          $size = 16777215;
        }
      }
      if ($autoIncrement) {
        $allowsNull = FALSE;
      } elseif (isset($row['notnull']) && (int)$row['notnull'] !== 0) {
        $allowsNull = FALSE;
        if (isset($row['dflt_value'])) {
          $default = $row['dflt_value'];
        }
        if (0 === strpos($default, "'") && substr($default, -1) === "'") {
          $default = substr($default, 1, -1);
        }
      } else {
        $allowsNull = TRUE;
      }
      return new FieldStructure(
        $row['name'],
        $type,
        $size,
        $autoIncrement,
        $allowsNull,
        $default
      );
    }

    /**
     * @param TableStructure $tableStructure
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable(TableStructure $tableStructure, $tablePrefix = '') {
      $table = $this->getIdentifier($tableStructure->name, $tablePrefix);
      /** @var IndexFieldStructure $primaryKeyField */
      if (
        ($primaryIndex = $tableStructure->indizes->getPrimary()) && count($primaryIndex->fields) === 1
      ) {
        $primaryKeyField = $primaryIndex->fields->first();
      } else {
        $primaryKeyField = NULL;
      }
      $sql = 'CREATE TABLE '.$this->getQuotedIdentifier($tableStructure->name)." (\n";
      $parameters = [];
      /** @var FieldStructure $field */
      foreach ($tableStructure->fields as $field) {
        $fieldType = $this->getFieldType(
          $field,
          $field->name === $primaryKeyField->name
        );
        $sql .= '  '.$this->getQuotedIdentifier($field->name).' '.$fieldType[0].",\n";
        array_push($parameters, ...$fieldType[1]);
      }
      /** @var IndexStructure $index */
      foreach ($tableStructure->indizes as $index) {
        if (
          $index->isPrimary() && count($index->fields) > 1
        ) {
          $sql .= 'PRIMARY KEY ('.implode(',', $this->getQuotedIdentifiers($index->fields->keys()))."),\n";
        } elseif ($index->isUnique && !$index->isPrimary()) {
          $sql .= 'CONSTRAINT '.$this->getQuotedIdentifier($tableStructure->name.'_'.$index->name).' UNIQUE ';
          $sql .= '('.implode(',', $this->getQuotedIdentifiers($index->fields->keys()))."),\n";
        }
      }
      $sql = substr($sql, 0, -2)."\n)\n";
      if ($this->_connection->execute(new SQLStatement($sql, $parameters)) !== FALSE) {
        /** @var IndexStructure $index */
        foreach ($tableStructure->indizes as $index) {
          $this->addIndex($table, $index);
        }
        return TRUE;
      }
      return FALSE;
    }

    /**
     * get field type
     *
     * @param FieldStructure $field
     * @param bool $primaryKey
     * @return array
     */
    private function getFieldType($field, $primaryKey = FALSE) {
      if ($primaryKey && $field->isAutoIncrement) {
        return ['INTEGER PRIMARY KEY', []];
      }
      $parameters = [];
      if ($field->allowsNull) {
        $default = NULL;
        $notNullStr = '';
      } else {
        $default = '';
        $notNullStr = ' NOT NULL';
      }
      $default = $field->defaultValue;
      $defaultStr = '';
      if (isset($default)) {
        switch ($field->type) {
        case FieldStructure::TYPE_INTEGER:
          $defaultStr = ' DEFAULT '.(int)$default;
          break;
        case FieldStructure::TYPE_DECIMAL:
          $defaultStr = ' DEFAULT '.(float)$default;
          break;
        default:
          $defaultStr = ' DEFAULT '.$this->_connection->quoteString($default);
          break;
        }
      }
      switch ($field->type) {
      case FieldStructure::TYPE_INTEGER:
        $size = ($field->size > 0) ? (int)$field->size : 1;
        if ($size <= 2) {
          $result = 'SMALLINT';
        } elseif ($size <= 4) {
          $result = 'INTEGER';
        } else {
          $result = 'BIGINT';
        }
        break;
      case FieldStructure::TYPE_DECIMAL:
        if (is_array($field->size) && count($field->size) === 2) {
          list($before, $after) = $field->size;
          $result = 'NUMERIC('.$before.','.$after.')';
        } else {
          $result = 'NUMERIC('.(int)$field->size.',0)';
        }
        break;
      case FieldStructure::TYPE_TEXT:
      default:
        $size = ($field->size > 0) ? (int)$field->size : 1;
        if ($size <= 4) {
          $result = 'CHAR('.$size.')';
        } elseif ($size <= 255) {
          $result = 'VARCHAR('.$size.')';
        } else {
          $result = 'TEXT';
        }
        break;
      }
      if ($primaryKey) {
        $result .= ' PRIMARY KEY';
      }
      return [
        $result.$defaultStr.$notNullStr,
        $parameters
      ];
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
      $key = $this->getIndexInfo($tableName, $indexStructure->name);
      if ($dropCurrent && $key) {
        $this->dropIndex($tableName, $indexStructure['name']);
      }
      $fields = '('.implode(',', $this->getQuotedIdentifiers($indexStructure->fields->keys())).')';
      $quotedTableName = $this->getQuotedIdentifier($tableName);
      $quotedIndexName = $this->getQuotedIdentifier($tableName.'_'.$indexStructure->name);
      if ($indexStructure->isPrimary) {
        $sql = '';
      } elseif ($indexStructure->isUnique) {
        $sql = 'CREATE UNIQUE INDEX '.$quotedIndexName.' ON '.$quotedTableName.' '.$fields;
      } else {
        $sql = 'CREATE INDEX '.$quotedIndexName.' ON '.$quotedTableName.' '.$fields;
      }
      return (!$sql || ($this->_connection->execute($sql) !== FALSE));
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return IndexStructure
     */
    private function getIndexInfo($tableName, $indexName) {
      $result = NULL;
      if ($indexName === IndexStructure::PRIMARY) {
        $sql = 'PRAGMA table_info('.$this->getQuotedIdentifier($tableName).');';
        if ($res = $this->_connection->execute($sql)) {
          while ($row = $res->fetchAssoc()) {
            if ((int)$row['pk'] === 1) {
              if (!isset($result)) {
                $result = new IndexStructure(
                  IndexStructure::PRIMARY,
                  TRUE
                );
              }
              $result->fields[] = new IndexFieldStructure($row['name']);
            }
          }
        }
      } else {
        $sql = 'PRAGMA index_list('.$this->getQuotedIdentifier($tableName).')';
        if ($res = $this->_connection->execute($sql)) {
          while ($row = $res->fetchAssoc()) {
            if (
              strpos($row['name'], $tableName) === 0 &&
              $indexName === substr($row['name'], strlen($tableName) + 1)
            ) {
              $internalIndexName = $row['name'];
              $result = new IndexStructure($indexName, $row['unique'] === '1');
              $sql = "PRAGMA index_info(?')";
              if ($res = $this->_connection->execute(new SQLStatement($sql, [$internalIndexName]))) {
                while ($row = $res->fetchAssoc()) {
                  $result->fields[] = new IndexFieldStructure($row['name']);
                }
              }
            } else {
              continue;
            }
          }
        }
      }
      return $result;
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName) {
      $sql = 'PRAGMA index_list('.$this->getQuotedIdentifier($tableName).')';
      if ($dbResult = $this->_connection->execute($sql)) {
        $internalName = NULL;
        while ($row = $dbResult->fetchAssoc()) {
          if ($row['origin'] === 'pk' || $row['origin'] === 'u') {
            continue;
          }
          if (
            strpos($row['name'], $indexName) === 0 ||
            strpos($row['name'], $tableName.'_'.$indexName) === 0
          ) {
            $internalName = $row['name'];
          } else {
            continue;
          }
        }
        if (NULL !== $internalName) {
          $sql = 'DROP INDEX '.$this->getQuotedIdentifier($internalName);
          return ($this->_connection->execute($sql) !== FALSE);
        }
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName) {
      return $this->alterTable($tableName, self::ACTION_DROP_FIELD, $fieldName);
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
        if (
          $expectedStructure->type === FieldStructure::TYPE_TEXT &&
          $expectedStructure->size > 255 && $currentStructure->size > 255
        ) {
          return FALSE;
        }
        if (
          $expectedStructure->type === FieldStructure::TYPE_INTEGER &&
          $currentStructure->isAutoIncrement &&
          $expectedStructure->isAutoIncrement
        ) {
          return FALSE;
        }
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
      $fieldsOverlap = array_intersect( $expectedStructure->fields->keys(), $currentStructure->fields->keys());
      return count($fieldsOverlap) !== count($expectedStructure->fields);
    }
  }
}
