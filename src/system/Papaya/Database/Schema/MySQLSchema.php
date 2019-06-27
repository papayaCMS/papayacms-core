<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\SQLStatement;

  class MySQLSchema extends AbstractSchema {

    /**
     * @return array
     */
    public function getTables() {
      $tables = [];
      if ($result = $this->connection->execute('SHOW TABLES')) {
        while($tableName = $result->fetchField()) {
          $tables[] = $tableName;
        }
      }
      return $tables;
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
      $table = $this->$this->getIdentifier($tableName, $tablePrefix);
      $sql = 'SHOW TABLE STATUS LIKE ?';
      $tableType = NULL;
      if (
        ($result = $this->connection->execute(new SQLStatement($sql, [$table]))) &&
        ($row = $result->fetchAssoc())
      ) {
        $tableType = (strtoupper($row['Engine']) === 'INNODB')
          ? 'transactions' : NULL;
      }
      $sql = "SHOW FIELDS FROM $table";
      if ($result = $this->connection->execute($sql)) {
        while ($row = $result->fetchAssoc()) {
          $fields[$row['Field']] = $this->parseFieldData($row);
        }
      }
      $keys = [];
      $sql = "SHOW KEYS FROM $table";
      if ($result = $this->connection->execute($sql)) {
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
     * @param array $tableStructure
     * @param string $tablePrefix
     * @access public
     * @return boolean
     */
    public function createTable(array $tableStructure, $tablePrefix = '') {
      $fulltextIndex = FALSE;
      if (
        isset($tableStructure['fields'], $tableStructure['name']) &&
        is_array($tableStructure['fields']) &&
        trim($tableStructure['name']) !== ''
      ) {
        $table = $this->getIdentifier($tableStructure['name'], $tablePrefix);
        $sql = "CREATE TABLE `$table` (\n";
        $parameters = [];
        $autoIncrementField = FALSE;
        foreach ($tableStructure['fields'] as $field) {
          $extra = $this->getFieldExtras($field, !$autoIncrementField);
          $sql .= '  `'.$this->getIdentifier($field['name']).'` '.
            $this->getFieldType($field['type'], $field['size']).
            $extra[0].",\n";
          array_push($parameters, ...$extra[1]);
        }
        if (isset($tableStructure['keys']) && is_array($tableStructure['keys'])) {
          if (isset($tableStructure['keys']['PRIMARY'])) {
            $key = $tableStructure['keys']['PRIMARY'];
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
          foreach ($tableStructure['keys'] as $keyName => $key) {
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
        } elseif (isset($tableStructure['type']) && $tableStructure['type'] === 'transactions') {
          $sql .= ' ENGINE=InnoDB';
        }
        $sql .= ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        return ($this->connection->execute(new SQLStatement($sql, $parameters)) !== FALSE);
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
     * @param bool $allowAutoIncrement
     * @return array SQL instruction and parameters
     */
    private function getFieldExtras($field, $allowAutoIncrement = FALSE) {
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
      if (isset($default)) {
        $defaultStr = ' DEFAULT ?';
        switch (strtolower($field['type'])) {
        case 'integer':
          $parameters[] = (int)$default;
          break;
        case 'float':
          $parameters[] = (float)$default;
          break;
        case 'string' :
          if ($field['size'] > 255) {
            $defaultStr = '';
          } else {
            $parameters[] = (string)$default;
          }
          break;
        }
      } else {
        $defaultStr = ' DEFAULT NULL';
      }
      if (
        $allowAutoIncrement &&
        isset($field['autoinc']) && $field['autoinc'] === 'yes'
      ) {
        $autoIncrementString = ' auto_increment';
        $defaultStr = '';
      } else {
        $autoIncrementString = '';
      }
      return [
        $defaultStr.$notNullStr.$autoIncrementString,
        $parameters
      ];
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function addField($tableName, array $fieldStructure) {
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` ADD COLUMN `%s` %s %s',
        $this->getIdentifier($tableName),
        $this->getIdentifier($fieldStructure['name']),
        $this->getFieldType($fieldStructure['type'], $fieldStructure['size']),
        $this->getFieldExtras($fieldStructure)
      );
      return ($this->connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     */
    public function changeField($tableName, array $fieldStructure) {
      $allowAutoIncrement = FALSE;
      if (isset($fieldStructure['autoinc']) && $fieldStructure['autoinc'] === 'yes') {
        $sql = 'SHOW COLUMNS FROM `'.$this->getIdentifier($tableName).'`';
        if ($result = $this->connection->execute($sql)) {
          $autoIncrementField = NULL;
          $fieldExists = FALSE;
          while ($row = $result->fetchAssoc()) {
            $isAutoIncrement = strtolower($row['Extra']) === 'auto_increment';
            if ($row['Field'] === $fieldStructure['name']) {
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
              $this->getIdentifier($tableName),
              $this->getIdentifier($autoIncrementField['name']),
              $this->getFieldType(
                $autoIncrementField['type'], $autoIncrementField['size']
              ),
              $this->getFieldExtras($autoIncrementField)
            );
            $this->connection->execute($sql);
          }
        }
      }
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` MODIFY COLUMN `%s` %s %s',
        $this->getIdentifier($tableName),
        $this->getIdentifier($fieldStructure['name']),
        $this->getFieldType($fieldStructure['type'], $fieldStructure['size']),
        $this->getFieldExtras($fieldStructure, $allowAutoIncrement)
      );
      return ($this->connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName) {
      $sql = sprintf(
      /** @lang text */
        'ALTER TABLE `%s` DROP COLUMN `%s`',
        $this->getIdentifier($tableName),
        $this->getIdentifier($fieldName)
      );
      return ($this->connection->execute($sql) !== FALSE);
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
      if (isset($indexStructure['fields']) && is_array($indexStructure['fields'])) {
        $sql = 'SHOW COLUMNS FROM `'.$this->getIdentifier($tableName).'`';
        if ($res = $this->connection->execute($sql)) {
          $needed = count($indexStructure['fields']);
          $existsInDatabase = 0;
          while ($row = $res->fetchAssoc()) {
            if (
              ++$existsInDatabase >= $needed &&
              in_array($row['Field'], $indexStructure['fields'], FALSE)
            ) {
              break;
            }
          }
          $res->free();
          if ($existsInDatabase >= $needed) {
            $fields = '(';
            foreach ($indexStructure['fields'] as $fieldName) {
              if (
                isset($indexStructure['keysize'][$fieldName]) &&
                $indexStructure['keysize'][$fieldName] > 0
              ) {
                $fields .= '`'.$this->getIdentifier($fieldName).'` ('.
                  (int)$indexStructure['keysize'][$fieldName].'), ';
              } else {
                $fields .= '`'.$this->getIdentifier($fieldName).'`, ';
              }
            }
            $fields = substr($fields, 0, -2).')';
            $drop = $dropCurrent
              ? ' DROP INDEX `'.$this->getIdentifier($indexStructure['name']).'`,' : '';
            if ($indexStructure['name'] === 'PRIMARY') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($tableName).'`'.$drop.
                ' ADD PRIMARY KEY '.$fields;
            } elseif (isset($indexStructure['fulltext']) && $indexStructure['fulltext'] === 'yes') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($tableName).'`'.$drop.
                ' ADD FULLTEXT `'.$this->getIdentifier($indexStructure['name']).'` '.$fields;
            } elseif (isset($indexStructure['unique']) && $indexStructure['unique'] === 'yes') {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($tableName).'`'.$drop.
                ' ADD UNIQUE `'.$this->getIdentifier($indexStructure['name']).'` '.$fields;
            } else {
              $sql = 'ALTER TABLE `'.$this->getIdentifier($tableName).'`'.$drop.
                ' ADD INDEX `'.$this->getIdentifier($indexStructure['name']).'` '.$fields;
            }
            return ($this->connection->execute($sql) !== FALSE);
          }
        }
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName) {
      if ($indexName === 'PRIMARY') {
        $sql =
          /** @lang text */
          'ALTER TABLE `'.$this->getIdentifier($tableName).'` DROP PRIMARY KEY';
      } else {
        $sql =
          /** @lang text */
          'ALTER TABLE `'.$this->getIdentifier($tableName).'` DROP INDEX `'.
          $this->getIdentifier($indexName).'`';
      }
      return ($this->connection->execute($sql) !== FALSE);
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
        return TRUE;
      }
      if ($expectedStructure['null'] !== $currentStructure['null']) {
        return TRUE;
      }
      if ($expectedStructure['autoinc'] === 'yes' && $currentStructure['autoinc'] !== 'yes') {
        return TRUE;
      }
      if ($expectedStructure['default'] !== $currentStructure['default']) {
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
      if ($expectedStructure['fulltext'] === 'yes' && $currentStructure['fulltext'] !== 'yes') {
        return TRUE;
      }
      if (count(array_diff_assoc($expectedStructure['keysize'], $currentStructure['keysize'])) > 0) {
        return TRUE;
      }
      if (
        count(array_diff($expectedStructure['fields'], $currentStructure['fields'])) > 0
      ) {
        return TRUE;
      }
      return FALSE;
    }
  }
}
