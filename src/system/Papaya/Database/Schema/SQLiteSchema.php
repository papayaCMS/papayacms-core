<?php

namespace Papaya\Database\Schema {

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
          $this->connection->execute(
            "SELECT name FROM sqlite_master WHERE type = 'table'"
          )
        )
      );
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function addField($tableName, array $fieldStructure) {
      return $this->changeField($tableName, $fieldStructure);
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function changeField($tableName, array $fieldStructure) {
      return $this->alterTable($tableName, self::ACTION_CHANGE_FIELD, $fieldStructure);
    }

    /**
     * Change table structure
     *
     * @param string $tableName
     * @param string $action
     * @param array|string $fieldData
     * @return boolean
     */
    private function alterTable($tableName, $action, $fieldData) {
      // get current table definitions
      $structure = $this->describeTable($tableName);
      // create temporary table with old table definitions
      $temporaryTableName = 'tmp_'.$tableName;
      $temporaryTableStructure = [
        'name' => $temporaryTableName,
        'keys' => $structure['keys'],
        'fields' => $structure['fields'],
      ];
      $this->connection->execute(
        sprintf('DROP TABLE IF EXISTS "%s"', $temporaryTableName)
      );
      $this->createTable($temporaryTableStructure);
      // copy data from old table to temporary table (insert select)
      $this->connection->execute(
        sprintf(
          'INSERT INTO "%1$s" (%2$s) SELECT %2$s FROM "%3$s"',
          $temporaryTableName,
          implode(
            ',',
            array_map(
              static function ($fieldName) {
                return '"'.$fieldName.'"';
              },
              array_keys($structure['fields'])
            )
          ),
          $tableName
        )
      );
      // drop old table
      $this->connection->execute(sprintf('DROP TABLE "%s"', $tableName));

      // calculate new table definitions
      $newTableStructure = [
        'name' => $tableName,
        'keys' => $structure['keys'],
      ];
      switch ($action) {
      case self::ACTION_DROP_FIELD:
        foreach ($structure['fields'] as $fieldName => $field) {
          if ($fieldData !== $fieldName) {
            $newTableStructure['fields'][$fieldName] = $field;
          }
        }
        unset($structure['fields'][$fieldData]);
        break;
      case self::ACTION_CHANGE_FIELD:
        $newTableStructure['fields'] = $structure['fields'];
        $newTableStructure['fields'][$fieldData['name']] = $fieldData;
        break;
      }

      // create new table with new table definitions
      $this->createTable($newTableStructure);
      // copy data from temporary table to new table (insert select)
      $result = (bool)$this->connection->execute(
        sprintf(
          'INSERT INTO "%1$s" (%2$s) SELECT %2$s FROM "%3$s"',
          $tableName,
          implode(
            ',',
            array_map(
              static function ($fieldName) {
                return '"'.$fieldName.'"';
              },
              array_keys($structure['fields'])
            )
          ),
          $temporaryTableName
        )
      );
      // drop temporary table
      $this->$this->_connector->execute(sprintf('DROP TABLE %s', $temporaryTableName));
      return $result;
    }

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return array
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $fields = [];
      $keys = [];
      $table = $this->getIdentifier($tableName, $tablePrefix);

      if ($res = $this->connection->execute('PRAGMA table_info("'.$table.'");')) {
        while ($row = $res->fetchAssoc()) {
          $fields[$row['name']] = $this->parseFieldData($row);
          if ($row['pk'] > 0) {
            if (isset($keys['PRIMARY']) && is_array($keys['PRIMARY'])) {
              $keys['PRIMARY']['fields'][$row['pk']] = $row['name'];
            } else {
              $keys['PRIMARY']['orgname'] = 'PRIMARY';
              $keys['PRIMARY']['name'] = 'PRIMARY';
              $keys['PRIMARY']['unique'] = 'yes';
              $keys['PRIMARY']['fields'] = [$row['pk'] => $row['name']];
              $keys['PRIMARY']['fulltext'] = 'no';
              $keys['PRIMARY']['autoinc'] = ($row['type'] === 'INTEGER')
                ? 'yes' : 'no';
            }
            ksort($keys['PRIMARY']['fields']);
          }
        }
      }
      if ($res = $this->connection->execute('PRAGMA index_list("'.$table.'")')) {
        while ($row = $res->fetchAssoc()) {
          if ($row['origin'] === 'pk' || $row['origin'] === 'u') {
            continue;
          }
          if (strpos($row['name'], $table) === 0) {
            $keyName = substr($row['name'], strlen($table) + 1);
          } else {
            $keyName = $row['name'];
          }
          $keys[$keyName]['orgname'] = $row['name'];
          $keys[$keyName]['name'] = $keyName;
          $keys[$keyName]['unique'] = ((string)$row['unique'] === '1') ? 'yes' : 'no';
          $keys[$keyName]['fields'] = [];
          $keys[$keyName]['fulltext'] = 'no';
        }
        foreach ($keys as $keyName => $keyData) {
          $sql = 'PRAGMA index_info("'.$this->getIdentifier($keyData['orgname']).'")';
          if ($res = $this->connection->execute($sql)) {
            while ($row = $res->fetchAssoc()) {
              $keys[$keyName]['fields'][] = $row['name'];
            }
          }
        }
      }
      return [
        'name' => $tableName,
        'fields' => $fields,
        'keys' => $keys
      ];
    }

    /**
     * Parse SQLite field data
     *
     * @param array $row
     * @access private
     * @return array
     */
    private function parseFieldData($row) {
      $autoIncrement = FALSE;
      $default = NULL;
      switch ($row['type']) {
      case 'BIGSERIAL':
      case 'BIGINT':
        $type = 'integer';
        $size = 8;
        break;
      case 'SERIAL':
      case 'INT':
      case 'INTEGER':
        $type = 'integer';
        $size = 4;
        if ((int)$row['pk'] === 1) {
          $autoIncrement = TRUE;
        }
        break;
      case 'SMALLINT':
        $type = 'integer';
        $size = 2;
        break;
      case 'TEXT':
        $type = 'string';
        $size = 65535;
        break;
      default:
        if (preg_match('(NUMERIC\((\d+,\d+)\))', $row['type'], $regs)) {
          $type = 'float';
          $size = $regs[1];
        } elseif (preg_match('(VARCHAR\((\d+)\))', $row['type'], $regs)) {
          $type = 'string';
          $size = (int)$regs[1];
        } elseif (preg_match('(CHAR\((\d+)\))', $row['type'], $regs)) {
          $type = 'string';
          $size = (int)$regs[1];
        } else {
          $type = 'string';
          $size = 16777215;
        }
      }
      if ($autoIncrement) {
        $notNull = TRUE;
      } elseif (isset($row['notnull']) && (int)$row['notnull'] !== 0) {
        $notNull = TRUE;
        if (isset($row['dflt_value'])) {
          $default = $row['dflt_value'];
        }
        if (0 === strpos($default, "'") && substr($default, -1) === "'") {
          $default = substr($default, 1, -1);
        }
      } else {
        $notNull = FALSE;
      }
      $result = [
        'name' => $row['name'],
        'type' => $type,
        'size' => $size,
        'null' => $notNull ? 'no' : 'yes',
        'autoinc' => $autoIncrement ? 'yes' : 'no',
        'default' => $default
      ];

      return $result;
    }

    /**
     * @param array $tableStructure
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable(array $tableStructure, $tablePrefix = '') {
      if (is_array($tableStructure['fields']) && trim($tableStructure['name']) !== '') {
        $table = $this->getIdentifier($tableStructure['name'], $tablePrefix);
        if (
          isset($tableStructure['keys']['PRIMARY']['fields']) &&
          is_array($tableStructure['keys']['PRIMARY']['fields']) &&
          count($tableStructure['keys']['PRIMARY']['fields']) === 1
        ) {
          $primaryKeyField = reset($tableStructure['keys']['PRIMARY']['fields']);
        } else {
          $primaryKeyField = '';
        }
        $sql = "CREATE TABLE \"$table\" (\n";
        $parameters = [];
        foreach ($tableStructure['fields'] as $field) {
          $fieldType = $this->getFieldType(
            $field,
            $field['name'] === $primaryKeyField
          );
          $sql .= '  '.$this->getIdentifier($field['name']).' '.$fieldType[0].",\n";
          array_push($parameters, ...$fieldType[1]);
        }
        if (is_array($tableStructure['keys'])) {
          foreach ($tableStructure['keys'] as $keyName => $key) {
            if (
              $keyName === 'PRIMARY' &&
              is_array($key['fields']) &&
              count($key['fields']) > 1
            ) {
              $sql .= 'PRIMARY KEY ('.implode(',', $key['fields'])."),\n";
            } elseif (
              $keyName !== 'PRIMARY' &&
              isset($key['unique']) &&
              $key['unique'] === 'yes'
            ) {
              $sql .= "CONSTRAINT \"{$table}_{$keyName}\" UNIQUE ";
              $sql .= '('.implode(',', $key['fields'])."),\n";
            }
          }
        }
        $sql = substr($sql, 0, -2)."\n)\n";
        if ($this->connection->execute(new SQLStatement($sql, $parameters)) !== FALSE) {
          foreach ($tableStructure['keys'] as $key) {
            $this->addIndex($table, $key);
          }
          return TRUE;
        }
      }
      return FALSE;
    }

    /**
     * get field type
     *
     * @param array $field
     * @param bool $primaryKey
     * @return array
     */
    private function getFieldType($field, $primaryKey = FALSE) {
      if ($primaryKey && isset($field['autoinc']) && $field['autoinc'] === 'yes') {
        return ['INTEGER PRIMARY KEY', []];
      }
      $parameters = [];
      if (isset($field['null']) && $field['null'] === 'yes') {
        $default = NULL;
        $notNullStr = '';
      } else {
        $default = '';
        $notNullStr = ' NOT NULL';
      }
      if (isset($field['default'])) {
        $default = $field['default'];
      }
      $defaultStr = '';
      if (isset($default)) {
        $defaultStr = ' DEFAULT ?';
        switch (strtolower($field['type'])) {
        case 'integer':
          $parameters[] = (int)$default;
          break;
        case 'float':
          $parameters[] = (float)$default;
          break;
        default:
          $parameters[] = (string)$default;
          break;
        }
      }
      switch (strtolower(trim($field['type']))) {
      case 'integer':
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 2) {
          $result = 'SMALLINT';
        } elseif ($size <= 4) {
          $result = 'INTEGER';
        } else {
          $result = 'BIGINT';
        }
        break;
      case 'float':
        if (FALSE !== strpos($field['size'], ',')) {
          list($before, $after) = explode(',', $field['size']);
          $before = ($before > 0) ? (int)$before : 1;
          $after = (int)$after;
          if ($after > $before) {
            $before += $after;
          }
          $result = 'NUMERIC('.$before.','.$after.')';
        } else {
          $result = 'NUMERIC('.(int)$field['size'].',0)';
        }
        break;
      case 'string':
      default:
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 4) {
          $result = 'CHAR('.$field['size'].')';
        } elseif ($size <= 255) {
          $result = 'VARCHAR('.$field['size'].')';
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
     * @param array $indexStructure
     * @return bool
     */
    public function addIndex($tableName, array $indexStructure) {
      return $this->changeIndex($tableName, $indexStructure, FALSE);
    }

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($tableName, array $indexStructure, $dropCurrent = TRUE) {
      $key = $this->getIndexInfo($tableName, $indexStructure['name']);
      if ($dropCurrent && $key) {
        $this->dropIndex($tableName, $indexStructure['name']);
      }
      if (isset($indexStructure['fields']) && is_array($indexStructure['fields'])) {
        $fields = '('.implode(',', $indexStructure['fields']).')';
        $indexName = $this->getIdentifier($tableName.'_'.$indexStructure['name']);
        if ($indexStructure['name'] === 'PRIMARY') {
          //$sql = "ALTER TABLE ".$this->escapeString($table)." ADD PRIMARY KEY ".$fields;
          $sql = '';
        } elseif (isset($indexStructure['unique']) && $indexStructure['unique'] === 'yes') {
          $sql = 'CREATE UNIQUE INDEX "'.$indexName.'" ON '.$this->getIdentifier($tableName).' '.$fields;
        } else {
          $sql = 'CREATE INDEX "'.$indexName.'" ON '.$this->getIdentifier($tableName).' '.$fields;
        }
        return (!$sql || ($this->connection->execute($sql) !== FALSE));
      }
      return FALSE;
    }

    /**
     * Get index info
     *
     * @param string $tableName
     * @param string $indexName
     * @access public
     * @return array $result
     */
    private function getIndexInfo($tableName, $indexName) {
      $result = FALSE;
      if ($indexName === 'PRIMARY') {
        $sql = 'PRAGMA table_info("'.$this->getIdentifier($tableName).'");';
        if ($res = $this->connection->execute($sql)) {
          while ($row = $res->fetchAssoc()) {
            if ((int)$row['pk'] === 1) {
              if (!is_array($result)) {
                $result['name'] = 'PRIMARY';
                $result['unique'] = 1;
                $result['fields'] = [$row['name']];
                $result['fulltext'] = 'no';
                $result['autoinc'] = ($row['type'] === 'INTEGER') ? 'yes' : 'no';
              } else {
                $result['fields'][] = $row['name'];
              }
            }
          }
        }
      } else {
        $keyName = $this->getIdentifier($indexName);
        $sql = 'PRAGMA index_list("'.$this->getIdentifier($tableName).'")';
        if ($res = $this->connection->execute($sql)) {
          while ($row = $res->fetchAssoc()) {
            if (
              strpos($row['name'], $tableName) === 0 &&
              $keyName === substr($row['name'], strlen($tableName) + 1)
            ) {
              $result['orgname'] = $row['name'];
              $result['name'] = $keyName;
              $result['unique'] = ($row['unique'] === '1') ? 'yes' : 'no';
              $result['fields'] = [];
              $result['fulltext'] = 'no';
              $sql = "PRAGMA index_info(?')";
              if ($res = $this->connection->execute(new SQLStatement($sql, [$result['orgname']]))) {
                while ($row = $res->fetchAssoc()) {
                  $result['fields'][] = $row['name'];
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
      $sql = 'PRAGMA index_list("'.$this->getIdentifier($tableName).'")';
      if ($res = $this->connection->execute($sql)) {
        $keyName = NULL;
        $keys = [];
        while ($row = $res->fetchAssoc()) {
          if ($row['origin'] === 'pk' || $row['origin'] === 'u') {
            continue;
          }
          if (
            strpos($row['name'], $indexName) === 0 ||
            strpos($row['name'], $tableName.'_'.$indexName) === 0
          ) {
            $keyName = $row['name'];
          } else {
            continue;
          }
          $keys[$keyName]['orgname'] = $row['name'];
          $keys[$keyName]['name'] = $keyName;
        }
        if ($keyName && $keys[$keyName]) {
          $sql = 'DROP INDEX '.$keys[$keyName]['orgname'];
          return ($this->connection->execute($sql) !== FALSE);
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
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     */
    public function isFieldDifferent(array $expectedStructure, array $currentStructure) {
      if ($expectedStructure['type'] !== $currentStructure['type']) {
        return TRUE;
      }
      if ((int)$expectedStructure['size'] !== (int)$currentStructure['size']) {
        if (
          $expectedStructure['type'] === 'string' &&
          $expectedStructure['size'] > 255 &&
          $currentStructure['size'] > 255
        ) {
          return FALSE;
        }
        if (
          $expectedStructure['type'] === 'integer' &&
          $currentStructure['autoinc'] === 'yes' &&
          $expectedStructure['autoinc'] === 'yes'
        ) {
          return FALSE;
        }
        return TRUE;
      }
      if (
        $expectedStructure['autoinc'] === 'yes' &&
        $currentStructure['autoinc'] !== 'yes'
      ) {
        return TRUE;
      }
      if (
        ($expectedStructure['default'] !== $currentStructure['default']) &&
        !(empty($expectedStructure['default']) && empty($currentStructure['default']))
      ) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     */
    public function isIndexDifferent(array $expectedStructure, array $currentStructure) {
      if (
        ($expectedStructure['unique'] === 'yes' || $expectedStructure['name'] === 'PRIMARY') !==
        ($currentStructure['unique'] === 'yes' || $currentStructure['name'] === 'PRIMARY')
      ) {
        return TRUE;
      }
      if (
        count(array_intersect($expectedStructure['fields'], $currentStructure['fields'])) !==
        count($expectedStructure['fields'])
      ) {
        return TRUE;
      }
      return FALSE;
    }
  }
}
