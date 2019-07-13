<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Schema\Structure\FieldStructure;
  use Papaya\Database\Schema\Structure\IndexFieldStructure;
  use Papaya\Database\Schema\Structure\IndexStructure;
  use Papaya\Database\Schema\Structure\TableStructure;
  use Papaya\Database\SQLStatement;

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
          $this->_connection->execute($sql)
        )
      );
    }

    /**
     * @param string $tableName
     * @param string $tablePrefix
     * @return TableStructure
     */
    public function describeTable($tableName, $tablePrefix = '') {
      $prefixedTableName = $this->getIdentifier($tableName, $tablePrefix);
      $table = new TableStructure($tableName, $tablePrefix !== '');
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
               AND a.attnum > 0
               AND a.atttypid = t.oid
               AND a.attrelid = c.oid
             ORDER BY a.attnum";
      if ($result = $this->_connection->execute($sql, [$prefixedTableName])) {
        while ($row = $result->fetchAssoc()) {
          $table->fields[] = $this->parseFieldData($row);
        }
      }
      $sql = "SELECT ic.relname AS index_name, a.attname AS column_name,
                   i.indisunique AS unique_key, i.indisprimary AS primary_key,
                   idx
              FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a,
                   generate_series(0,current_setting('max_index_keys')::integer-1) idx
             WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
               AND i.indkey[idx] = a.attnum
               AND a.attrelid = bc.oid
               AND bc.relname = ?
             ORDER BY a.attnum";
      if ($result = $this->_connection->execute($sql, [$prefixedTableName])) {
        $indexFields = [];
        while ($row = $result->fetchAssoc()) {
          if ($row['primary_key'] === 't') {
            $keyName = 'PRIMARY';
          } elseif (strpos($row['index_name'], $prefixedTableName) === 0) {
            $keyName = substr($row['index_name'], strlen($prefixedTableName) + 1);
          } else {
            $keyName = $row['index_name'];
          }
          if (!isset($table->indizes[$keyName])) {
            $table->indizes[$keyName] = new IndexStructure(
              $keyName,
              $row['unique_key'] === 't'
            );
          }
          $indexFields[$keyName][$row['idx']] = $row;
        }
        foreach ($indexFields as $indexName => $fields) {
          ksort($fields);
          foreach ($fields as $field) {
            $table->indizes[$indexName]->fields[] = new IndexFieldStructure($field['column_name']);
          }
        }
      }
      return $table;
    }


    /**
     * @param array $row
     * @access private
     * @return FieldStructure
     */
    private function parseFieldData($row) {
      $autoIncrement = FALSE;
      if (0 === strpos($row['field_type'], 'int')) {
        $type = FieldStructure::TYPE_INTEGER;
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
      } elseif ($row['field_type'] === 'varchar' || $row['field_type'] === 'bpchar') {
        $type = FieldStructure::TYPE_TEXT;
        $size = substr(
          $row['format_type'],
          strrpos($row['format_type'], '(') + 1,
          -1
        );
      } elseif ($row['field_type'] === 'text') {
        $type = FieldStructure::TYPE_TEXT;
        $size = 65535;
      } elseif ($row['field_type'] === 'numeric') {
        $type = FieldStructure::TYPE_DECIMAL;
        $size = substr($row['format_type'], 8, -1);
      } else {
        $type = FieldStructure::TYPE_TEXT;
        $size = 16777215;
      }
      $stringFieldPattern = '(^\'(([^\']+|\\\\)+)\'::(character varying|bpchar)$)i';
      $numericFieldPattern = '(^(\d+)(::(smallint|integer|bigint|int|numeric))?$)i';
      $default = NULL;
      if (
        preg_match('(^nextval\(\'[\w\.]+\'::text\)$)i', $row['default_value'], $matches) ||
        preg_match("(^nextval\(\(\'[\w\.]+\'::text\)::regclass\)$)i", $row['default_value'], $matches) ||
        preg_match("(^nextval\(\'[\w\.]+\'::regclass\)$)i", $row['default_value'], $matches)
      ) {
        $autoIncrement = TRUE;
      } elseif (preg_match($stringFieldPattern, $row['default_value'], $matches)) {
        if ((string)$matches[1] !== '') {
          $default = $matches[1];
        }
      } elseif (preg_match($numericFieldPattern, $row['default_value'], $matches)) {
        if ((string)$matches[1] !== '0' || (strtolower($row['not_null']) === 't')) {
          $default = $matches[1];
        }
      }
      return new FieldStructure(
        $row['field_name'],
        $type,
        $size,
        $autoIncrement,
        strtolower($row['not_null']) === 't',
        !$autoIncrement ? $default : NULL
      );
    }

    /**
     * @param TableStructure $tableStructure
     * @param string $tablePrefix
     * @return bool
     */
    public function createTable(TableStructure $tableStructure, $tablePrefix = '') {
      if (count($tableStructure->fields) > 0) {
        $prefixedTableName = $this->getIdentifier($tableStructure->name, $tablePrefix);
        $sql = 'CREATE TABLE '.$this->getQuotedIdentifier($prefixedTableName).' ('."\n";
        $parameters = [];
        /** @var FieldStructure $field */
        foreach ($tableStructure->fields as $field) {
          $fieldType = $this->getFieldTypeSQL($field);
          $sql .= '  '.$this->getQuotedIdentifier($field->name).' '.$fieldType.",\n";
        }
        if ($primary = $tableStructure->indizes->getPrimary()) {
          $sql .= sprintf(
            "CONSTRAINT %s PRIMARY KEY (%s),\n",
            $this->getQuotedIdentifier(strtolower($prefixedTableName).'_primary_key'),
            implode(',', $this->getQuotedIdentifiers($primary->fields->keys()))
          );
        }
        /** @var IndexStructure $index */
        foreach ($tableStructure->indizes as $indexName => $index) {
          if (!$index->isPrimary && $index->isUnique) {
            $sql .= sprintf(
              "CONSTRAINT %s UNIQUE (%s),\n",
              $this->getQuotedIdentifier(strtolower($prefixedTableName).'_'.$indexName),
              implode(',', $this->getQuotedIdentifiers($index->fields->keys()))
            );
          }
        }
        $sql = substr($sql, 0, -2)."\n)\n";
        if ($this->_connection->execute(new SQLStatement($sql, $parameters)) !== FALSE) {
          /** @var IndexStructure $index */
          foreach ($tableStructure->indizes as $index) {
            if (!($index->isPrimary || $index->isUnique)) {
              $this->addIndex($prefixedTableName, $index);
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
     * @param FieldStructure $field
     * @return array
     */
    private function getFieldTypeArray(FieldStructure $field) {
      if ($field->isAutoIncrement) {
        return [
          'type' => ($field->size > 4) ? 'BIGINT' : 'INTEGER',
          'not_null' => 'NOT NULL',
          'default' => 'DEFAULT 1'
        ];
      }
      $parameters = [];
      if ($field->allowsNull) {
        $default = NULL;
        $notNullStr = '';
      } else {
        $default = '';
        $notNullStr = 'NOT NULL';
      }
      if (!empty($field->defaultValue)) {
        $default = $field->defaultValue;
      }
      $defaultStr = '';
      if (isset($default)) {
        $defaultStr = 'DEFAULT ?';
        switch ($field->type) {
        case FieldStructure::TYPE_INTEGER:
          $defaultStr = 'DEFAULT '.((int)$default);
          break;
        case FieldStructure::TYPE_DECIMAL:
          $defaultStr = 'DEFAULT '.((float)$default);
          break;
        default:
          $defaultStr = 'DEFAULT '.$this->_connection->quoteString($default);
          break;
        }
      }
      switch ($field->size) {
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
        list($before, $after) = explode(',', $field->size);
        $result = 'NUMERIC('.$before.','.$after.')';
        break;
      case 'FieldStructure::TYPE_TEXT':
      default :
        $size = ($field->size > 0) ? (int)$field->size : 1;
        if ($size <= 255) {
          $result = 'VARCHAR('.$size.')';
        } else {
          $result = 'TEXT';
        }
        break;
      }
      return [
        'type' => $result,
        'default' => $defaultStr,
        'not_null' => $notNullStr
      ];
    }

    /**
     * @param FieldStructure $field
     * @return string
     */
    public function getFieldTypeSQL(FieldStructure $field) {
      if ($field->isAutoIncrement) {
        return ($field->size > 4) ? 'BIGSERIAL' : 'SERIAL';
      }
      $data = $this->getFieldTypeArray($field);
      return $data['type'].' '.$data['default'].' '.$data['not_null'];
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
      if ($dropCurrent) {
        $this->dropIndex($tableName, $indexStructure->name);
      }
      if (count($indexStructure->fields) > 0) {
        if ($indexStructure->isPrimary) {
          $sql = sprintf(
            'ALTER TABLE %s ADD PRIMARY KEY (%s)',
            $this->getQuotedIdentifier($tableName),
            implode(',', $this->getQuotedIdentifiers($indexStructure->fields->keys()))
          );
        } else {
          $sql = sprintf(
            'CREATE %s INDEX %s ON %s (%s)',
            $indexStructure->isUnique ? 'UNIQUE' : '',
            $this->getQuotedIdentifier($tableName.'_'.$indexStructure->name),
            $this->getQuotedIdentifier($tableName),
            implode(',', $this->getQuotedIdentifiers($indexStructure->fields->keys()))
          );
        }
        return ($this->_connection->execute($sql) !== FALSE);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    public function dropIndex($tableName, $indexName) {
      if ($indexInfo = $this->getIndexInfo($tableName, $indexName)) {
        $quotedIndexName = $this->getQuotedIdentifier($indexInfo['name']);
        if ($indexInfo['primary']) {
          $sql = 'ALTER TABLE '.$this->getQuotedIdentifier($tableName).' DROP CONSTRAINT '.$quotedIndexName;
        } else {
          $sql = 'DROP INDEX '.$quotedIndexName;
        }
        return ($this->_connection->execute($sql) !== FALSE);
      }
      return FALSE;
    }

    /**
     * Get index information
     *
     * @param string $tableName
     * @param string $indexName
     * @access public
     * @return array|NULL $result
     */
    private function getIndexInfo($tableName, $indexName) {
      $result = FALSE;
      $keyName = ($indexName === IndexStructure::PRIMARY) ? 'primary_key' : $this->getIdentifier($indexName);
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
        $tableName, $tableName.'_'.$indexName
      ];
      if (
        ($res = $this->_connection->execute(new SQLStatement($sql, $parameters))) &&
        ($row = $res->fetchAssoc())
      ) {
        if ($row['primary_key'] === 't') {
          return [
            'name' => IndexStructure::PRIMARY,
            'primary' => TRUE
          ];
        }
        return [
          'name' => $row['index_name'],
          'primary' => FALSE
        ];
      }
      return NULL;
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
      $tableName = $this->getIdentifier($tableName);
      $fieldName = $this->getIdentifier($fieldStructure->name);
      if ($databaseField = $this->getFieldInfo($tableName, $fieldStructure->name)) {
        if ($this->isFieldDifferent($fieldStructure, $databaseField)) {
          $alterSQL = sprintf(
          /** @lang TEXT */
            'ALTER TABLE %s ALTER COLUMN %s ',
            $this->getQuotedIdentifier($tableName),
            $this->getQuotedIdentifier($fieldName)
          );
          $sqlData = $this->getFieldTypeArray($fieldStructure);
          $sql = '';
          if ($fieldStructure->isAutoIncrement) {
            $sql .= $alterSQL.sprintf(
                "SET DEFAULT nextval(%s::text);\n",
                $this->getQuotedIdentifier("public.{$tableName}_{$fieldName}_seq")
              );
          } elseif (!empty($sqlData['default'])) {
            $sql .= $alterSQL.sprintf("SET %s::%s;\n", $sqlData['default'], $sqlData['type']);
          }
          $sql .= $alterSQL.sprintf(
              'TYPE %1$s USING %2$s::%1$s;'."\n", $sqlData['type'], $this->getQuotedIdentifier($fieldName)
            );
          $sql .= $alterSQL.(empty($sqlData['not_null']) ? ' DROP NOT NULL ' : ' SET NOT NULL ').";\n";
          return ($this->_connection->execute(new SQLStatement($sql, $parameters)) !== FALSE);
        }
        return TRUE;
      }
      $fieldType = $this->getFieldTypeSQL($fieldStructure);
      $sql = sprintf(
        "ALTER TABLE %s ADD COLUMN %s %s;\n",
        $this->getQuotedIdentifier($tableName),
        $this->getQuotedIdentifier($fieldName),
        $fieldType
      );
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * @param string $table
     * @param string $fieldName
     * @return FieldStructure|NULL
     */
    private function getFieldInfo($table, $fieldName) {
      $sql = "SELECT a.attname AS field_name,
                   t.typname AS field_type,
                   pg_catalog.format_type(a.atttypid, a.atttypmod),
                   a.attlen AS field_size,
                   a.attnotNull AS not_null,
                   (
                     SELECT substring(d.adsrc FOR 128)
                       FROM pg_catalog.pg_attrdef d
                      WHERE d.adrelid = a.attrelid
                        AND d.adnum = a.attnum
                        AND a.atthasdef
                   ) AS default_value
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
        ($res = $this->_connection->execute(new SQLStatement($sql, $parameters))) &&
        ($row = $res->fetchAssoc())
      ) {
        return $this->parseFieldData($row);
      }
      return NULL;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    public function dropField($tableName, $fieldName) {
      $sql = sprintf(
        'ALTER TABLE %s DROP COLUMN %s',
        $this->getQuotedIdentifier($tableName),
        $this->getQuotedIdentifier($fieldName)
      );
      return ($this->_connection->execute($sql) !== FALSE);
    }

    /**
     * Compare the field structure
     *
     * @param FieldStructure $expectedStructure
     * @param FieldStructure $currentStructure
     * @return boolean different
     */
    public function isFieldDifferent(FieldStructure $expectedStructure, FieldStructure $currentStructure) {
      if ($expectedStructure->type !== $currentStructure->type) {
        return TRUE;
      }
      if ($expectedStructure->size !== $currentStructure->size) {
        if (
          $expectedStructure->type === FieldStructure::TYPE_TEXT &&
          $expectedStructure->size > 255 &&
          $currentStructure->size > 255
        ) {
          return FALSE;
        }
        if (
          $expectedStructure->type === FieldStructure::TYPE_INTEGER &&
          $currentStructure->isAutoIncrement &&
          $expectedStructure->isAutoIncrement &&
          (
            ($expectedStructure->size === 2 && $currentStructure->size === 4) ||
            ($expectedStructure->size === 6 && $currentStructure->size === 8)
          )
        ) {
          return FALSE;
        }
        return TRUE;
      }
      if ($expectedStructure->allowsNull !== $currentStructure->allowsNull) {
        return TRUE;
      }
      if ($expectedStructure->isAutoIncrement !== $currentStructure->isAutoIncrement) {
        return TRUE;
      }
      if (
        !(empty($expectedStructure->defaultValue) && empty($currentStructure->defaultValue)) &&
        $expectedStructure->defaultValue !== $currentStructure->defaultValue
      ) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param IndexStructure $expectedStructure
     * @param IndexStructure $currentStructure
     * @return boolean
     */
    public function isIndexDifferent(IndexStructure $expectedStructure, IndexStructure $currentStructure) {
      if (
        ($expectedStructure->isUnique || $expectedStructure->isPrimary) !==
        ($currentStructure->isUnique || $currentStructure->isPrimary)
      ) {
        return TRUE;
      }
      if (
        count(array_intersect($expectedStructure->fields->keys(), $currentStructure->fields->keys())) !==
        count($expectedStructure->fields)
      ) {
        return TRUE;
      }
      return FALSE;
    }
  }
}
