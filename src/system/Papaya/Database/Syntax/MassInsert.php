<?php

namespace Papaya\Database\Syntax {

  use Papaya\Database\Connection;

  class MassInsert {

    /**
     * @var \Papaya\Database\Connection
     */
    private $_connection;

    /**
     * @var
     */
    private $_tableName;
    /**
     * @var array
     */
    private $_values;

    public function __construct(Connection $connection, $tableName, array $values) {
      $this->_connection = $connection;
      $this->_tableName = $tableName;
      $this->_values = $values;
    }

    public function __invoke() {
      $connection = $this->_connection;
      $baseSQL = 'INSERT INTO '.$connection->escapeString($this->_tableName).' ';
      $valueString = '';
      $currentFields = NULL;
      $maxQuerySize = 524288;
      if (count($this->_values) > 0) {
        foreach ($this->_values as $data) {
          if (is_array($data) && count($data) > 0) {
            $quotedFields = [];
            $quotedValues = [];
            foreach ($data as $fieldName => $fieldValue) {
              $quotedFields[] = $connection->quoteIdentifier($fieldName);
              $quotedValues[] = $connection->quoteString($fieldValue);
            }
            if (!isset($currentFields)) {
              $valueString = '('.implode(',', $quotedValues).'), ';
              $currentFields = $quotedFields;
            } elseif (strlen($valueString) > $maxQuerySize) {
              if (trim($valueString) !== '') {
                $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
                  substr($valueString, 0, -2);
                if (FALSE === $connection->execute($sql)) {
                  return FALSE;
                }
              }
              $valueString = '('.implode(',', $quotedValues).'), ';
              $currentFields = $quotedFields;
            } elseif (count(array_diff($quotedFields, $currentFields)) === 0) {
              $valueString .= '('.implode(',', $quotedValues).'), ';
            } else {
              if (trim($valueString) !== '') {
                $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
                  substr($valueString, 0, -2);
                if (FALSE === $connection->execute($sql)) {
                  return FALSE;
                }
              }
              $valueString = "('".implode("','", $quotedValues)."'), ";
              $currentFields = $quotedFields;
            }
          }
        }
        if (trim($valueString) !== '') {
          $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
            substr($valueString, 0, -2);
          if (FALSE !== $connection->execute($sql)) {
            return TRUE;
          }
        }
      }
      return FALSE;
    }
  }
}
