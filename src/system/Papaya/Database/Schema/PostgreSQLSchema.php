<?php

namespace Papaya\Database\Schema {

  class PostgreSQLSchema extends AbstractSchema {

    /**
     * @return array
     */
    public function getTables() {
      $sql = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";
      return array_map(
        static function ($row) {
          return $row['tablename'];
        },
        iterator_to_array(
          $this->_connector->execute($sql)
        )
      );
    }

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return array
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $fields = [];
      $table = $this->getIdentifier($tableName, $tablePrefix);
      $sql = "SELECT a.attname AS fieldname,
                   t.typname AS fieldtype,
                   pg_catalog.format_type(a.atttypid, a.atttypmod),
                   a.attlen AS fieldsize,
                   a.attnotNull AS not_null,
                   (SELECT substring(d.adsrc for 128)
              FROM pg_catalog.pg_attrdef d
             WHERE d.adrelid = a.attrelid
               AND d.adnum = a.attnum
               AND a.atthasdef) AS defaultwert
              FROM pg_class c, pg_attribute a, pg_type t
             WHERE relkind = 'r'
               AND c.relname='$table'
               AND a.attnum > 0
               AND a.atttypid = t.oid
               AND a.attrelid = c.oid
             ORDER BY a.attnum";
      if ($result = $this->_connector->execute($sql)) {
        while ($row = $result->fetchAssoc()) {
          $fields[$row['fieldname']] = $this->parseFieldData($row);
        }
      }
      $keys = [];

      $sql = "SELECT ic.relname AS index_name, a.attname AS column_name,
                   i.indisunique AS unique_key, i.indisprimary AS primary_key,
                   idx
              FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a,
                   generate_series(0,current_setting('max_index_keys')::integer-1) idx
             WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
               AND i.indkey[idx] = a.attnum
               AND a.attrelid = bc.oid
               AND bc.relname = '$table'
             ORDER BY a.attnum";
      if ($result = $this->_connector->execute($sql)) {
        while ($row = $result->fetchAssoc()) {
          if ($row['primary_key'] === 't') {
            $keyName = 'PRIMARY';
          } elseif (strpos($row['index_name'], $table) === 0) {
            $keyName = substr($row['index_name'], strlen($table) + 1);
          } else {
            $keyName = $row['index_name'];
          }
          $keys[$keyName]['orgname'] = $row['index_name'];
          $keys[$keyName]['name'] = $keyName;
          $keys[$keyName]['unique'] = ($row['unique_key'] === 't') ? 'yes' : 'no';
          $keys[$keyName]['fields'][$row['idx']] = $row['column_name'];
          ksort($keys[$keyName]['fields']);
          $keys[$keyName]['fulltext'] = 'no';
        }
      }
      return [
        'name' => $tableName,
        'fields' => $fields,
        'keys' => $keys
      ];
    }


    /**
     * Parse PostgreSQL field data
     *
     * @param array $row
     * @access private
     * @return array
     */
    private function parseFieldData($row) {
      $autoIncrement = 0;
      if (0 === strpos($row['fieldtype'], 'int')) {
        $type = 'integer';
        switch ($row['format_type']) {
        case 'bigint':
          $size = 8;
          break;
        case 'integer':
          $size = 4;
          break;
        case 'smallint':
          $size = 2;
          break;
        default :
          $size = 8;
        }
      } elseif ($row['fieldtype'] === 'varchar' || $row['fieldtype'] === 'bpchar') {
        $type = 'string';
        $size = substr(
          $row['format_type'],
          strrpos($row['format_type'], '(') + 1,
          -1
        );
      } elseif ($row['fieldtype'] === 'text') {
        $type = 'string';
        $size = 65535;
      } elseif ($row['fieldtype'] === 'numeric') {
        $type = 'float';
        $size = substr($row['format_type'], 8, -1);
      } else {
        $size = 16777215;
        $type = 'string';
      }
      $stringFieldPattern = '(^\'(([^\']+|\\\\)+)\'::(character varying|bpchar)$)i';
      $numericFieldPattern = '(^(\d+)(::(smallint|integer|bigint|int|numeric))?$)i';
      if (
        preg_match('(^nextval\(\'[\w\.]+\'::text\)$)i', $row['defaultwert'], $matches) ||
        preg_match("(^nextval\(\(\'[\w\.]+\'::text\)::regclass\)$)i", $row['defaultwert'], $matches) ||
        preg_match("(^nextval\(\'[\w\.]+\'::regclass\)$)i", $row['defaultwert'], $matches)
      ) {
        $autoIncrement = 1;
        $default = 1;
      } elseif (preg_match($stringFieldPattern, $row['defaultwert'], $matches)) {
        if ((string)$matches[1] !== '') {
          $default = $matches[1];
        }
      } elseif (preg_match($numericFieldPattern, $row['defaultwert'], $matches)) {
        if ((string)$matches[1] !== '0' || (strtolower($row['not_null']) === 't')) {
          $default = $matches[1];
        }
      }
      $result = [
        'name' => $row['fieldname'],
        'type' => $type,
        'size' => $size,
        'null' => (strtolower($row['not_null']) === 't') ? 'no' : 'yes',
        'autoinc' => ($autoIncrement > 0) ? 'yes' : 'no',
        'default' => NULL
      ];
      if ((!$autoIncrement) && isset($default)) {
        $result['default'] = $default;
      }
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
        $sql = 'CREATE TABLE "'.$table.'" ('."\n";
        $parameters = [];
        foreach ($tableStructure['fields'] as $field) {
          $fieldType = $this->getFieldType($field);
          $sql .= '  "'.$this->getIdentifier($field['name']).'" '.
            $fieldType[0].",\n";
          array_push($parameters, ...$fieldType[1]);
        }
        if (isset($tableStructure['keys']) && is_array($tableStructure['keys'])) {
          if (isset($tableStructure['keys']['PRIMARY'])) {
            $sql .= 'CONSTRAINT "'.$this->getIdentifier(strtolower($table)).
              '_primary_key" PRIMARY KEY ('.
              implode(',', $tableStructure['keys']['PRIMARY']['fields'])."),\n";
          }
          foreach ($tableStructure['keys'] as $keyName => $key) {
            if (
              $keyName !== 'PRIMARY' &&
              isset($key['unique']) && $key['unique'] === 'yes'
            ) {
              $sql .= 'CONSTRAINT "'.$this->getIdentifier(strtolower($table)).'_'.
                $keyName.'" UNIQUE ';
              $sql .= '('.implode(',', $key['fields'])."),\n";
            }
          }
        }
        $sql = substr($sql, 0, -2)."\n)\n";
        if ($this->_connector->execute($sql, $parameters) !== FALSE) {
          if (isset($tableStructure['keys']) && is_array($tableStructure['keys'])) {
            foreach ($tableStructure['keys'] as $key) {
              $this->addIndex($table, $key);
            }
          }
          return TRUE;
        }
      }
      return FALSE;
    }

    /**
     * PostgresSQL field type
     *
     * @param array $field
     * @param bool $separated
     * @return array
     */
    private function getFieldType($field, $separated = FALSE) {
      $parameters = [];
      if (isset($field['autoinc']) && $field['autoinc'] === 'yes') {
        if ($separated) {
          $result['type'] = ($field['size'] > 4) ? 'BIGINT' : 'INTEGER';
          $result['not_null'] = 'NOT NULL';
          $result['default'] = 'DEFAULT 1';
          $result['autoinc'] = TRUE;
          return [$result, []];
        }
        return [($field['size'] > 4) ? 'BIGSERIAL' : 'SERIAL', []];
      }
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
      default :
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 255) {
          $result = 'VARCHAR('.$field['size'].')';
        } else {
          $result = 'TEXT';
        }
        break;
      }
      if ($separated) {
        return [
          [
            'type' => $result,
            'default' => $defaultStr,
            'not_null' => $notNullStr,
            'autoinc' => FALSE
          ],
          $parameters
        ];
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
        if ($indexStructure['name'] === 'PRIMARY') {
          $sql = 'ALTER TABLE "'.$this->getIdentifier($tableName).'" ADD PRIMARY KEY '.$fields;
        } elseif (isset($indexStructure['unique']) && $indexStructure['unique'] === 'yes') {
          $sql = 'CREATE UNIQUE INDEX "'.$this->getIdentifier($tableName).'_'.
            $this->getIdentifier($indexStructure['name']).'" ON "'.$this->getIdentifier($tableName).'" '.
            $fields;
        } else {
          $sql = 'CREATE INDEX "'.$this->getIdentifier($tableName).'_'.
            $this->getIdentifier($indexStructure['name']).'" ON "'.
            $this->getIdentifier($tableName).'" '.$fields;
        }
        return ($this->_connector->execute($sql) !== FALSE);
      }
      return FALSE;
    }

    /**
     * Get index information
     *
     * @param string $table
     * @param string $key
     * @access public
     * @return array|FALSE $result
     */
    private function getIndexInfo($table, $key) {
      $result = FALSE;
      $tableName = $this->getIdentifier($table);
      $keyName = ($key === 'PRIMARY') ? 'primary_key' : $this->getIdentifier($key);
      $sql = 'SELECT ic.relname, ic.relname AS index_name, a.attname AS column_name,
                   i.indisunique AS unique_key, i.indisprimary AS primary_key
              FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a
             WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
               AND (i.indkey[0] = a.attnum OR
                   i.indkey[1] = a.attnum OR
                   i.indkey[2] = a.attnum OR
                   i.indkey[3] = a.attnum OR
                   i.indkey[4] = a.attnum OR
                   i.indkey[5] = a.attnum OR
                   i.indkey[6] = a.attnum OR
                   i.indkey[7] = a.attnum)
               AND a.attrelid = bc.oid
               AND bc.relname = ?
               AND ic.relname = ?
             ORDER BY a.attnum';
      $parameters = [
        $tableName, $tableName.'_'.$keyName
      ];
      if (
        ($res = $this->_connector->execute($sql, $parameters)) &&
        ($row = $res->fetchAssoc())
      ) {
        if ($row['primary_key'] === 't') {
          $keyName = 'PRIMARY';
        } elseif (strpos($row['index_name'], $table) === 0) {
          $keyName = substr($row['index_name'], strlen($table) + 1);
        } else {
          $keyName = $row['index_name'];
        }
        $result['orgname'] = $row['index_name'];
        $result['name'] = $keyName;
        $result['unique'] = ($row['unique_key'] === 't') ? 'yes' : 'no';
        $result['fields'][] = $row['column_name'];
        $result['fulltext'] = 'no';
      }
      return $result;
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName) {
      if ($key = $this->getIndexInfo($tableName, $indexName)) {
        $keyName = $this->getIdentifier($key['orgname']);
        if ($key['name'] === 'PRIMARY') {
          $sql = 'ALTER TABLE "'.$tableName.'" DROP CONSTRAINT "'.$keyName.'"';
        } else {
          $sql = 'DROP INDEX "'.$keyName.'"';
        }
        return ($this->_connector->execute($sql) !== FALSE);
      }
      return FALSE;
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
      $tableName = $this->getIdentifier($tableName);
      $fieldName = $this->getIdentifier($fieldStructure['name']);

      $xmlType = $this->getFieldType($fieldStructure);

      //check field exists in db
      if ($databaseField = $this->getFieldInfo($tableName, $fieldStructure['name'])) {
        $dbType = $this->getFieldType($databaseField);
        if ($xmlType !== $dbType) {
          $sqlData = $this->getFieldType($fieldStructure, TRUE);
          $sql = '';
          $parameters = [];
          if ($sqlData['autoinc']) {
            $sql .= "ALTER TABLE \"$tableName\"
                     ALTER COLUMN \"$fieldName\"
                     SET DEFAULT nextval(?::text);\n";
            $parameters[] = "public.{$tableName}_{$fieldName}_seq";
          } elseif (!empty($sqlData['default'])) {
            $sql .= "ALTER TABLE \"$tableName\"
                     ALTER COLUMN \"$fieldName\"
                       SET ?::{$sqlData['type']};\n";
            $parameters[] = $sqlData['default'];
          }
          $sql .= "ALTER TABLE \"$tableName\"
                   ALTER COLUMN \"$fieldName\"
                    TYPE {$sqlData['type']} USING \"$fieldName\"::{$sqlData['type']};\n";
          $sql .= "ALTER TABLE \"$tableName\"
                   ALTER COLUMN \"$fieldName\"\n";
          $sql .= empty($sqlData['not_null']) ? ' DROP NOT NULL ' : ' SET NOT NULL ';
          $sql .= ";\n";
          return ($this->_connector->execute($sql, $parameters) !== FALSE);
        }
        return TRUE;
      }
      $sql = "ALTER TABLE \"$tableName\"
              ADD COLUMN \"$fieldName\" $xmlType;\n";
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * Get field information
     *
     * @param string $table
     * @param string $fieldName
     * @access public
     * @return mixed array with row or boolean FALSE
     */
    private function getFieldInfo($table, $fieldName) {
      $sql = "SELECT a.attname AS fieldname,
                   t.typname AS fieldtype,
                   pg_catalog.format_type(a.atttypid, a.atttypmod),
                   a.attlen AS fieldsize,
                   a.attnotNull AS not_null,
                   (SELECT substring(d.adsrc FOR 128)
              FROM pg_catalog.pg_attrdef d
             WHERE d.adrelid = a.attrelid
               AND d.adnum = a.attnum
               AND a.atthasdef) AS defaultwert
              FROM pg_class c, pg_attribute a, pg_type t
             WHERE relkind = 'r'
               AND c.relname = ?
               AND a.attname = ?
               AND a.attnum > 0
               AND a.atttypid = t.oid
               AND a.attrelid = c.oid
             ORDER BY a.attnum";
      $parameters = [
        $table, $fieldName
      ];
      if (
        ($res = $this->_connector->execute($sql, $parameters)) &&
        ($row = $res->fetchAssoc())
      ) {
        return $this->parseFieldData($row);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName) {
      $sql = sprintf(
        'ALTER TABLE "%s" DROP COLUMN "%s"',
        $this->getIdentifier($tableName),
        $this->getIdentifier($fieldName)
      );
      return ($this->_connector->execute($sql) !== FALSE);
    }

    /**
     * Compare the field structure
     *
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return boolean different
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
          $expectedStructure['autoinc'] === 'yes' &&
          (
            ((int)$expectedStructure['size'] === 2 && (int)$currentStructure['size'] === 4) ||
            ((int)$expectedStructure['size'] === 6 && (int)$currentStructure['size'] === 8)
          )
        ) {
          return FALSE;
        }
        return TRUE;
      }
      if ($expectedStructure['null'] !== $currentStructure['null']) {
        return TRUE;
      }
      if ($expectedStructure['autoinc'] === 'yes' && $currentStructure['autoinc'] !== 'yes') {
        return TRUE;
      }
      if (
        !(empty($expectedStructure['default']) && empty($currentStructure['default'])) &&
        $expectedStructure['default'] !== $currentStructure['default']
      ) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Compare key structure
     *
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return boolean
     */
    public function isIndexDifferent(array $expectedStructure, array $currentStructure) {
      $result = FALSE;
      if (
        ($expectedStructure['unique'] === 'yes' || $expectedStructure['name'] === 'PRIMARY') !==
        ($currentStructure['unique'] === 'yes' || $currentStructure['name'] === 'PRIMARY')
      ) {
        $result = TRUE;
      } elseif (
        count(array_intersect($expectedStructure['fields'], $currentStructure['fields'])) !== count(
          $expectedStructure['fields']
        )
      ) {
        $result = TRUE;
      }
      return $result;
    }
  }
}
