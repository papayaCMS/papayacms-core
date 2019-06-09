<?php

namespace Papaya\Database\Schema {

  class MySQLSchema extends AbstractSchema {

    /**
     * @return array
     */
    public function getTables() {
      return array_map(
        static function ($row) {
          return $row[0];
        },
        iterator_to_array(
          $this->_connector->execute('SHOW TABLES')
        )
      );
    }

    /**
     * Query table structure
     *
     * @param string $tableName
     * @param string $tablePrefix optional, default value ''
     * @access public
     * @return array
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $fields = [];
      if ($tablePrefix) {
        $table = $tablePrefix.'_'.$tableName;
      } else {
        $table = $tableName;
      }
      $sql = 'SHOW TABLE STATUS LIKE ?';
      $tableType = NULL;
      if (
        ($result = $this->_connector->execute($sql, [$table])) &&
        ($row = $result->fetchAssoc())
      ) {
        $tableType = (strtoupper($row['Engine']) === 'INNODB')
          ? 'transactions' : NULL;
      }
      $sql = "SHOW FIELDS FROM $table";
      if ($result = $this->_connector->execute($sql)) {
        while ($row = $result->fetchAssoc()) {
          $fields[$row['Field']] = $this->parseFieldData($row);
        }
      }
      $keys = [];
      $sql = "SHOW KEYS FROM $table";
      if ($result = $this->_connector->execute($sql)) {
        while ($row = $result->fetchAssoc()) {
          $keyName = $row['Key_name'];
          $keys[$keyName]['name'] = $keyName;
          $keys[$keyName]['unique'] = ((int)$row['Non_unique'] === 0) ? 'yes' : 'no';
          $keys[$keyName]['fields'][$row['Seq_in_index']] = $row['Column_name'];
          if (isset($row['Sub_part'])) {
            $keys[$keyName]['keysize'][$row['Column_name']] = (int)$row['Sub_part'];
          } elseif (
            $fields[$row['Column_name']] === 'string' &&
            $fields[$row['Column_name']] > 255
          ) {
            $keys[$keyName]['keysize'][$row['Column_name']] = 255;
          } else {
            $keys[$keyName]['keysize'][$row['Column_name']] = 0;
          }
          if (isset($row['Index_type']) && $row['Index_type'] === 'FULLTEXT') {
            $keys[$keyName]['fulltext'] = 'yes';
          } elseif ($row['Comment'] === 'FULLTEXT') {
            $keys[$keyName]['fulltext'] = 'yes';
          } else {
            $keys[$keyName]['fulltext'] = 'no';
          }
        }
      }
      return [
        'name' => $tableName,
        'type' => $tableType,
        'fields' => $fields,
        'keys' => $keys
      ];
    }

    private function parseFieldData(array $row) {
      $type = $this->parseFieldType($row['Type']);
      $autoIncrement = (strtolower($row['Extra']) === 'auto_increment');
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
      return [
        'name' => $row['Field'],
        'type' => $type[0],
        'size' => $type[1],
        'null' => (strtolower($row['Null']) === 'yes') ? 'yes' : 'no',
        'default' => isset($default) ? (string)$default : NULL,
        'autoinc' => $autoIncrement ? 'yes' : 'no'
      ];
    }

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
        $type = 'integer';
        $size = 2;
        break;
      case 'MEDIUMINT':
      case 'INT':
      case 'INTEGER':
        $type = 'integer';
        $size = 4;
        break;
      case 'BIGINT':
        $size = 8;
        $type = 'integer';
        break;
      case 'FLOAT':
      case 'DOUBLE':
      case 'REAL':
      case 'DECIMAL':
      case 'NUMERIC':
        $type = 'float';
        break;
      case 'TINYTEXT':
        $type = 'string';
        $size = '255';
        break;
      case 'TEXT':
        $type = 'string';
        $size = '65535';
        break;
      case 'MEDIUMTEXT':
        $type = 'string';
        $size = '16777215';
        break;
      case 'LONGTEXT':
        $type = 'string';
        $size = '4294967295';
        break;
      case 'CHAR':
      case 'VARCHAR':
      default :
        $type = 'string';
        break;
      }
      return [$type, $size];
    }

    /**
     * @param array $tableData
     * @param string $tablePrefix
     * @access public
     * @return boolean
     */
    public function createTable($tableData, $tablePrefix) {
      $fulltextIndex = FALSE;
      if (
        isset($tableData['fields'], $tableData['name']) &&
        is_array($tableData['fields']) &&
        trim($tableData['name']) !== ''
      ) {
        $table = $this->getIdentifier($tableData['name'], $tablePrefix);
        $sql = "CREATE TABLE `$table` (\n";
        $parameters = [];
        $autoIncrementField = FALSE;
        foreach ($tableData['fields'] as $field) {
          $extra = $this->getFieldExtras($field, !$autoIncrementField);
          $sql .= '  `'.$this->getIdentifier($field['name']).'` '.
            $this->getFieldType($field['type'], $field['size']).
            $extra[0].",\n";
          array_push($parameters, ...$extra[1]);
        }
        if (isset($tableData['keys']) && is_array($tableData['keys'])) {
          if (isset($tableData['keys']['PRIMARY'])) {
            $key = $tableData['keys']['PRIMARY'];
            $fieldStr = '(';
            foreach ($key['fields'] as $fieldName) {
              if (
                isset($key['keysize'][$fieldName]) &&
                $key['keysize'][$fieldName] > 0
              ) {
                $fieldStr .= '`'.$this->getIdentifier($fieldName).'` ('.
                  (int)$key['keysize'][$fieldName].'), ';
              } else {
                $fieldStr .= '`'.$this->getIdentifier($fieldName).'`, ';
              }
            }
            $sql .= 'PRIMARY KEY '.substr($fieldStr, 0, -2)."),\n";
          }
          foreach ($tableData['keys'] as $keyName => $key) {
            if ($keyName !== 'PRIMARY') {
              if (isset($key['unique']) && $key['unique'] === 'yes') {
                $sql .= '  UNIQUE ';
              } elseif (isset($key['fulltext']) && $key['fulltext'] === 'yes') {
                $sql .= '  FULLTEXT ';
                $fulltextIndex = TRUE;
              } else {
                $sql .= ' KEY ';
              }
              $fieldStr = '(';
              foreach ($key['fields'] as $fieldName) {
                if (
                  isset($key['keysize'][$fieldName]) &&
                  $key['keysize'][$fieldName] > 0
                ) {
                  $fieldStr .= '`'.$this->getIdentifier($fieldName).'` ('.
                    (int)$key['keysize'][$fieldName].'), ';
                } else {
                  $fieldStr .= '`'.$this->getIdentifier($fieldName).'`, ';
                }
              }
              $sql .= '`'.$keyName.'` '.substr($fieldStr, 0, -2)."),\n";
            }
          }
        }
        $sql = substr($sql, 0, -2)."\n) ";
        if ($fulltextIndex) {
          $sql .= ' ENGINE=MyISAM';
        } elseif (isset($tableData['type']) && $tableData['type'] === 'transactions') {
          $sql .= ' ENGINE=InnoDB';
        }
        $sql .= ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        return ($this->_connector->execute($sql, $parameters) !== FALSE);
      }
      return FALSE;
    }

    /**
     * MySQL field type
     *
     * @param string $type
     * @param string|int $size
     * @access private
     * @return string
     */
    private function getFieldType($type, $size) {
      switch (strtolower(trim($type))) {
      case 'integer':
        $size = ($size > 0) ? (int)$size : 1;
        if ($size <= 2) {
          $result = 'TINYINT';
        } elseif ($size <= 4) {
          $result = 'INT';
        } else {
          $result = 'BIGINT';
        }
        break;
      case 'float':
        if (FALSE !== strpos($size, ',')) {
          list($before, $after) = explode(',', $size);
          $before = ($before > 0) ? (int)$before : 1;
          $after = (int)$after;
          if ($after > $before) {
            $before += $after;
          }
          $result = 'DECIMAL('.$before.','.$after.')';
        } else {
          $result = 'DECIMAL('.(int)$size.',0)';
        }
        break;
      case 'string':
      default:
        $size = ($size > 0) ? (int)$size : 1;
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
     * @param array $field
     * @param bool $allowAutoInrement
     * @access private
     * @return array
     */
    private function getFieldExtras($field, $allowAutoInrement = FALSE) {
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
      if (isset($default)) {
        $defaultStr = ' DEFAULT ?';
        switch (strtolower($field['type'])) {
        case 'integer':
          $default = (int)$default;
          break;
        case 'float':
          $default = (float)$default;
          break;
        case 'string' :
          if ($field['size'] > 255) {
            $defaultStr = '';
          }
          break;
        }
      } else {
        $defaultStr = ' DEFAULT NULL';
      }
      if (
        $allowAutoInrement &&
        isset($field['autoinc']) && $field['autoinc'] === 'yes'
      ) {
        $autoIncrementString = ' auto_increment';
        $defaultStr = '';
      } else {
        $autoIncrementString = '';
      }
      return [
        $defaultStr.$notNullStr.$autoIncrementString, $default
      ];
    }

    /**
     * @param string $table
     * @param array $fieldData
     * @return bool
     */
    public function addField($table, $fieldData) {
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` ADD COLUMN `%s` %s %s',
        $this->getIdentifier($table),
        $this->getIdentifier($fieldData['name']),
        $this->getFieldType($fieldData['type'], $fieldData['size']),
        $this->getFieldExtras($fieldData)
      );
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * @param string $table
     * @param array $fieldData
     * @return bool
     */
    public function changeField($table, $fieldData) {
      $allowAutoIncrement = FALSE;
      if (isset($fieldData['autoinc']) && $fieldData['autoinc'] === 'yes') {
        $sql = 'SHOW COLUMNS FROM `'.$this->getIdentifier($table).'`';
        if ($result = $this->_connector->execute($sql)) {
          $autoIncrementField = NULL;
          $fieldExists = FALSE;
          while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $isAutoIncrement = strtolower($row['Extra']) === 'auto_increment';
            if ($row['Field'] === $fieldData['name']) {
              $allowAutoIncrement = (trim($row['Key']) !== '');
              if ($isAutoIncrement) {
                unset($autoIncrementField);
                break;
              }
              $fieldExists = TRUE;
            } elseif ($isAutoIncrement) {
              $autoIncrementField = $this->parseFieldData($row);
            }
            if ($fieldExists && isset($autoIncrementField)) {
              break;
            }
          }
          $result->free();
          if (isset($autoIncrementField)) {
            $autoIncrementField['autoinc'] = 'no';
            $sql = sprintf(
            /** @lang text */
              'ALTER TABLE `%s` MODIFY COLUMN `%s` %s %s',
              $this->getIdentifier($table),
              $this->getIdentifier($autoIncrementField['name']),
              $this->getFieldType(
                $autoIncrementField['type'], $autoIncrementField['size']
              ),
              $this->getFieldExtras($autoIncrementField)
            );
            $this->_connector->execute($sql);
          }
        }
      }
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` MODIFY COLUMN `%s` %s %s',
        $this->getIdentifier($table),
        $this->getIdentifier($fieldData['name']),
        $this->getFieldType($fieldData['type'], $fieldData['size']),
        $this->getFieldExtras($fieldData, $allowAutoIncrement)
      );
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * @param string $table
     * @param string $field
     * @return bool
     */
    public function dropField($table, $field) {
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` DROP COLUMN `%s`',
        $this->getIdentifier($table),
        $this->getIdentifier($field)
      );
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * @param string $table
     * @param array $index
     * @return bool
     */
    public function addIndex($table, $index) {
      return $this->changeIndex($table, $index, FALSE);
    }

    /**
     * @param string $table
     * @param array $index
     * @param bool $dropCurrent
     * @return bool
     */
    public function changeIndex($table, $index, $dropCurrent = TRUE) {
      if (isset($index['fields']) && is_array($index['fields'])) {
        $sql = 'SHOW COLUMNS FROM `'.$this->getIdentifier($table).'`';
        if ($res = $this->_connector->execute($sql)) {
          $needed = count($index['fields']);
          $existsInDatabase = 0;
          while ($row = $res->fetchAssoc()) {
            if (
              ++$existsInDatabase >= $needed &&
              in_array($row['Field'], $index['fields'], FALSE)
            ) {
              break;
            }
          }
          $res->free();
          if ($existsInDatabase >= $needed) {
            $fields = '(';
            foreach ($index['fields'] as $fieldName) {
              if (
                isset($index['keysize'][$fieldName]) &&
                $index['keysize'][$fieldName] > 0
              ) {
                $fields .= '`'.$this->getIdentifier($fieldName).'` ('.
                  (int)$index['keysize'][$fieldName].'), ';
              } else {
                $fields .= '`'.$this->getIdentifier($fieldName).'`, ';
              }
            }
            $fields = substr($fields, 0, -2).')';
            $drop = $dropCurrent
              ? ' DROP INDEX `'.$this->getIdentifier($index['name']).'`,' : '';
            if ($index['name'] === 'PRIMARY') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($table).'`'.$drop.
                ' ADD PRIMARY KEY '.$fields;
            } elseif (isset($index['fulltext']) && $index['fulltext'] === 'yes') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($table).'`'.$drop.
                ' ADD FULLTEXT `'.$this->getIdentifier($index['name']).'` '.$fields;
            } elseif (isset($index['unique']) && $index['unique'] === 'yes') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($table).'`'.$drop.
                ' ADD UNIQUE `'.$this->getIdentifier($index['name']).'` '.$fields;
            } else {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($table).'`'.$drop.
                ' ADD INDEX `'.$this->getIdentifier($index['name']).'` '.$fields;
            }
            return ($this->_connector->execute($sql) !== FALSE);
          }
        }
      }
      return FALSE;
    }

    /**
     * @param string $table
     * @param string $name
     * @return bool
     */
    public function dropIndex($table, $name) {
      if ($name === 'PRIMARY') {
        $sql =
          /** @lang text */
          'ALTER TABLE `'.$this->getIdentifier($table).'` DROP PRIMARY KEY';
      } else {
        $sql =
          /** @lang text */
          'ALTER TABLE `'.$this->getIdentifier($table).'` DROP INDEX `'.
          $this->getIdentifier($name).'`';
      }
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * @param array $xmlField
     * @param array $databaseField
     * @return bool
     */
    public function isFieldStructureDifferent($xmlField, $databaseField) {
      if ($xmlField['type'] !== $databaseField['type']) {
        return TRUE;
      }
      if ($xmlField['size'] !== $databaseField['size']) {
        return TRUE;
      }
      if ($xmlField['null'] !== $databaseField['null']) {
        return TRUE;
      }
      if ($xmlField['autoinc'] === 'yes' && $databaseField['autoinc'] !== 'yes') {
        return TRUE;
      }
      if ($xmlField['default'] !== $databaseField['default']) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param array $xmlKey
     * @param array $databaseKey
     * @return bool
     */
    public function isKeyStructureDifferent($xmlKey, $databaseKey) {
      if (
        ($xmlKey['unique'] === 'yes' || $xmlKey['name'] === 'PRIMARY') !==
        ($databaseKey['unique'] === 'yes' || $databaseKey['name'] === 'PRIMARY')
      ) {
        return TRUE;
      }
      if ($xmlKey['fulltext'] === 'yes' && $databaseKey['fulltext'] !== 'yes') {
        return TRUE;
      }
      if (count(array_diff_assoc($xmlKey['keysize'], $databaseKey['keysize'])) > 0) {
        return TRUE;
      }
      if (
        count(array_diff($xmlKey['fields'], $databaseKey['fields'])) > 0
      ) {
        return TRUE;
      }
      return FALSE;
    }
  }
}
