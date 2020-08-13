<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Database\Statement {

  use Papaya\Database;
  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Statement as DatabaseStatement;

  /**
   * A client side prepared statement. All parameters have to be named.
   * Parameter type specific methods have to be used to add them. The
   * parameter names are prefixed with a colon in the SQL. Unlike
   * server side prepared statements it allows for list parameters and
   * table names as well.
   *
   * Example:
   *
   *  $statement = new Prepared(
   *    $databaseAccess
   *    'SELECT * FROM :tableOne WHERE id IN :IDs'
   *  );
   *  $statement->addTableName('tableOne', 'a_table');
   *  $statement->addIntList('IDs', [1,2,3]);
   *
   * @package Papaya\Database\Statement
   */
  class Prepared
    extends ExecutableStatement {

    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters = [];

    /**
     * @var NULL|array
     */
    private $_compiled = [
      'prepared' => NULL,
      'replaced' => NULL
    ];

    public function __construct(DatabaseConnection $databaseConnection, $sql) {
      parent::__construct($databaseConnection);
      $this->_sql = $sql;
    }

    public function __toString() {
      try {
        return $this->getSQLString(FALSE);
      } catch (\Exception $exception) {
      }
      return '';
    }

    /**
     * @param bool $allowPrepared
     * @return string
     */
    public function getSQLString($allowPrepared = TRUE) {
      return $this->compile($allowPrepared)->getSQLString($allowPrepared);
    }

    /**
     * @param bool $allowPrepared
     * @return array
     */
    public function getSQLParameters($allowPrepared = TRUE) {
      return $this->compile($allowPrepared)->getSQLParameters($allowPrepared);
    }

    /**
     * @param bool $allowPrepared
     * @return DatabaseStatement
     */
    private function compile($allowPrepared) {
      $mode = $allowPrepared ? 'prepared' : 'replaced';
      if (isset($this->_compiled[$mode])) {
        return $this->_compiled[$mode];
      }
      $quoteCharacters = ["'", '"', '`'];
      $patterns = [];
      foreach ($quoteCharacters as $quoteCharacter) {
        $patterns[] = \sprintf('(?:%1$s(?:[^%1$s]|\\\\%1$s|%1$s{2})*%1$s)', $quoteCharacter);
      }
      $pattern = '(('.\implode('|', $patterns).'))';
      $parts = \preg_split($pattern, $this->_sql, -1, PREG_SPLIT_DELIM_CAPTURE);
      $sql = '';
      $values = [];
      foreach ($parts as $part) {
        if (\in_array(\substr($part, 0, 1), $quoteCharacters, TRUE)) {
          $sql .= $part;
          continue;
        }
        $sql .= \preg_replace_callback(
          '(:(?<name>[a-zA-Z][a-zA-Z\\d_]*))',
          function($match) use ($allowPrepared, &$values) {
            $parameterName = \strtolower($match['name']);
            if (!\array_key_exists($parameterName, $this->_parameters)) {
              throw new \LogicException(\sprintf('Unknown parameter name: %s', $parameterName));
            }
            $parameter = $this->_parameters[$parameterName];
            if ($allowPrepared && $parameter['allow_prepared']) {
              $parameterValue = $parameter['value'];
              if (is_array($parameter['value'])) {
                if (count($parameter['value']) > 0) {
                  array_push($values, ...$parameterValue);
                  return '('.implode(', ', array_fill(0, count($parameterValue), '?')).')';
                }
                return '';
              }
              $values[] = $parameterValue;
              return '?';
            }
            return $this->encodeParameterValue($parameter['value'], $parameter['filter']);
          },
          $part
        );
      }
      return $this->_compiled[$mode] = new Database\SQLStatement($sql, $values);
    }

    /**
     * @param mixed $value
     * @param callable $filterFunction
     * @return string
     */
    private function encodeParameterValue($value, callable $filterFunction) {
      if (\is_array($value) || $value instanceof \Traversable) {
        $encodedValues = [];
        foreach ($value as $subValue) {
          if (NULL === $subValue) {
            continue;
          }
          if (\is_scalar($subValue)) {
            $encodedValues[] = $filterFunction($subValue);
          } elseif (\is_object($subValue) && \method_exists($subValue, '__toString')) {
            $encodedValues[] = $filterFunction((string)$subValue);
          } else {
            throw new \UnexpectedValueException(
              'Parameter list values need to be scalars or string castable.'
            );
          }
        }
        return '('.\implode(', ', $encodedValues).')';
      }
      return $filterFunction($value);
    }

    /**
     * @param string $parameterName
     * @param mixed $value
     * @param callable $filterFunction
     * @param bool $allowPrepared Allow parameter to be used in a server side prepared statement
     */
    private function addValue($parameterName, $value, callable $filterFunction, $allowPrepared) {
      $this->_compiled = NULL;
      $parameterName = $this->validateParameterName($parameterName);
      $this->_parameters[$parameterName] = [
        'value' => $value,
        'filter' => $filterFunction,
        'allow_prepared' => $allowPrepared
      ];
    }

    /**
     * @param string $parameterName
     * @param mixed $values
     * @param callable $filterFunction
     * @param bool $allowPrepared
     */
    private function addValueList($parameterName, $values, callable $filterFunction, $allowPrepared) {
      $this->addValue(
        $parameterName,
        \is_array($values) || $values instanceof \Traversable ? $values : [$values],
        $filterFunction,
        $allowPrepared
      );
    }

    /**
     * Throw exception for invalid parameter names and bound parameters
     *
     * @param string $parameterName
     * @return string
     */
    private function validateParameterName($parameterName) {
      $parameterName = \strtolower($parameterName);
      if (!\preg_match('(^[a-z][a-z\d_]*$)D', $parameterName)) {
        throw new \InvalidArgumentException(
          'Parameter name has to start with a letter and can only contain letters, digits and underscores.'
        );
      }
      if (\array_key_exists($parameterName, $this->_parameters)) {
        throw new \LogicException(\sprintf('Duplicate parameter name: %s', $parameterName));
      }
      return $parameterName;
    }

    /**
     * @param string $parameterName
     * @return $this
     */
    public function addNull($parameterName) {
      $this->addValue(
        $parameterName,
        NULL,
        static function() {
            return 'NULL';
        },
        FALSE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param string $value
     * @return $this
     */
    public function addString($parameterName, $value) {
      $this->addValue(
        $parameterName,
        $value,
        function($value) {
          return $this->getDatabaseConnection()->quoteString((string)$value);
        },
        TRUE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param string[]|string|\Traversable $values
     * @return $this
     */
    public function addStringList($parameterName, $values) {
      $this->addValueList(
        $parameterName,
        $values,
        function($value) {
          return $this->getDatabaseConnection()->quoteString((string)$value);
        },
        TRUE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param int $value
     * @return $this
     */
    public function addInt($parameterName, $value) {
      $this->addValue(
        $parameterName,
        $value,
        static function($value) {
          return (string)(int)$value;
        },
        TRUE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param int[]|int|\Traversable $values
     * @return $this
     */
    public function addIntList($parameterName, $values) {
      $this->addValueList(
        $parameterName,
        $values,
        static function($value) {
          return (string)(int)$value;
        },
        TRUE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param float $value
     * @param int $decimals
     * @return $this
     */
    public function addFloat($parameterName, $value, $decimals = 8) {
      $this->addValue(
        $parameterName,
        $value,
        static function($value) use ($decimals) {
          return \number_format((float)$value, $decimals);
        },
        TRUE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param bool $value
     * @return $this
     */
    public function addBool($parameterName, $value) {
      $this->addValue(
        $parameterName,
        $value,
        static function($value) {
          return $value ? 'true' : 'false';
        },
        FALSE
      );
      return $this;
    }

    /**
     * Adds a limit parameter if the limit value > 0. Otherwise
     * it will add an empty string for the parameter name.
     *
     * @param string $parameterName
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function addLimit($parameterName, $limit, $offset) {
      $this->addValue(
        $parameterName,
        NULL,
        function() use ($limit, $offset) {
          return $this->getDatabaseConnection()->syntax()->limit($limit, $offset);
        },
        FALSE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param string $tableName
     * @param bool $usePrefix
     * @return $this
     */
    public function addTableName($parameterName, $tableName, $usePrefix = TRUE) {
      $this->addValue(
        $parameterName,
        $tableName,
        function($tableName) use ($usePrefix) {
          return $this->getDatabaseConnection()->quoteIdentifier(
            $this->getDatabaseConnection()->getTableName($tableName, $usePrefix)
          );
        },
        FALSE
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @return bool
     */
    public function has($parameterName) {
      $parameterName = \strtolower($parameterName);
      return \array_key_exists($parameterName, $this->_parameters);
    }

    /**
     * @param string $parameterName
     */
    public function remove($parameterName) {
      $parameterName = \strtolower($parameterName);
      if (\array_key_exists($parameterName, $this->_parameters)) {
        unset($this->_parameters[$parameterName]);
      }
    }
  }
}
