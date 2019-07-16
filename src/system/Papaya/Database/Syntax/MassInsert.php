<?php

namespace Papaya\Database\Syntax {

  use Papaya\Database\Connection as DatabaseConnection;

  class MassInsert {

    /**
     * @var int
     */
    private $_maximumQuerySize = 524288;

    /**
     * @var \Papaya\Database\Connection
     */
    private $_connection;

    /**
     * @var string
     */
    private $_tableName;
    /**
     * @var array
     */
    private $_values;

    /**
     * @param \Papaya\Database\Connection $connection
     * @param $tableName
     * @param array $values
     */
    public function __construct(DatabaseConnection $connection, $tableName, array $values) {
      $this->_connection = $connection;
      $this->_tableName = $tableName;
      $this->_values = $values;
    }

    /**
     * Set the maximum allowed size of the SQL statement.
     *
     * @param int $size
     */
    public function setMaximumQuerySize($size) {
      $this->_maximumQuerySize = (int)$size;
    }

    /**
     * Execute the mass insert
     *
     * @return bool
     */
    public function __invoke() {
      $connection = $this->_connection;
      $baseSQL = 'INSERT INTO '.$connection->quoteIdentifier($this->_tableName).' ';
      $buffer = '';
      $currentFields = NULL;
      if (count($this->_values) > 0) {
        foreach ($this->_values as $data) {
          if (is_array($data) && count($data) > 0) {
            $quotedFields = [];
            $quotedValues = [];
            foreach ($data as $fieldName => $fieldValue) {
              $quotedFields[] = $connection->quoteIdentifier($fieldName);
              $quotedValues[] = $connection->quoteString($fieldValue);
            }
            // nothing in buffer yet, just add and continue
            if (!isset($currentFields)) {
              $buffer = '('.implode(',', $quotedValues).'), ';
              $currentFields = $quotedFields;
              continue;
            }
            // buffer filled, fields changed, execute buffer
            if (
              $buffer !== '' &&
              count(array_diff($quotedFields, $currentFields)) > 0
            ) {
              $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
                substr($buffer, 0, -2);
              if (FALSE === $connection->execute($sql)) {
                return FALSE;
              }
              $buffer = '';
            }
            $currentFields = $quotedFields;
            $currentValueString = '('.implode(',', $quotedValues).'), ';
            $querySize = strlen($baseSQL) + strlen($buffer) + strlen($currentValueString);
            if ($querySize >= $this->_maximumQuerySize) {
              $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
                substr($buffer, 0, -2);
              if (FALSE === $connection->execute($sql)) {
                return FALSE;
              }
              $buffer = '';
            }
            $buffer .= $currentValueString;
          }
        }
        if (trim($buffer) !== '') {
          $sql = $baseSQL.'('.implode(',', $currentFields).') VALUES '.
            substr($buffer, 0, -2);
          if (FALSE !== $connection->execute($sql)) {
            return TRUE;
          }
        }
      }
      return FALSE;
    }
  }
}
