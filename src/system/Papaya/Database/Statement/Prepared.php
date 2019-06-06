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
    extends Database\Statement {

    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters = [];

    public function __construct(Database\Access $databaseAccess, $sql) {
      parent::__construct($databaseAccess);
      $this->_sql = $sql;
    }

    public function __toString() {
      try {
        return $this->getSQL();
      } catch (\Exception $e) {
        return '';
      }
    }

    public function getSQL() {
      $quoteCharacters = ["'", '"', '`'];
      $patterns = [];
      foreach ($quoteCharacters as $quoteCharacter) {
        $patterns[] = \sprintf('(?:%1$s(?:[^%1$s]|\\\\%1$s|%1$s{2})*%1$s)', $quoteCharacter);
      }
      $pattern = '(('.\implode('|', $patterns).'))';
      $parts = \preg_split($pattern, $this->_sql, -1, PREG_SPLIT_DELIM_CAPTURE);
      $result = '';
      foreach ($parts as $part) {
        if (\in_array(\substr($part, 0, 1), $quoteCharacters, TRUE)) {
          $result .= $part;
          continue;
        }
        $result .= \preg_replace_callback(
          '(:(?<name>[a-zA-Z][a-zA-Z\d_]*))',
          function($match) {
            $parameterName = \strtolower($match['name']);
            if (!\array_key_exists($parameterName, $this->_parameters)) {
              throw new \LogicException(\sprintf('Unknown parameter name: %s', $parameterName));
            }
            return $this->encodeParameterValue(
              $this->_parameters[$parameterName]['value'],
              $this->_parameters[$parameterName]['filter']
            );
          },
          $part
        );
      }
      return $result;
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
     */
    private function addValue($parameterName, $value, callable $filterFunction) {
      $parameterName = $this->validateParameterName($parameterName);
      $this->_parameters[$parameterName] = ['value' => $value, 'filter' => $filterFunction];
    }

    /**
     * @param string $parameterName
     * @param mixed $values
     * @param callable $filterFunction
     */
    private function addValueList($parameterName, $values, callable $filterFunction) {
      $this->addValue(
        $parameterName,
        \is_array($values) || $values instanceof \Traversable ? $values : [$values],
        $filterFunction
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
      $this->addValue($parameterName, NULL, function() {
        return 'NULL';
      });
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
          return $this->_databaseAccess->quoteString((string)$value);
        }
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param string[]|string $values
     * @return $this
     */
    public function addStringList($parameterName, $values) {
      $this->addValueList(
        $parameterName,
        $values,
        function($value) {
          return $this->_databaseAccess->quoteString((string)$value);
        }
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
        function($value) {
          return (string)(int)$value;
        }
      );
      return $this;
    }

    /**
     * @param string $parameterName
     * @param int[]|int $values
     * @return $this
     */
    public function addIntList($parameterName, $values) {
      $this->addValueList(
        $parameterName,
        $values,
        function($value) {
          return (string)(int)$value;
        }
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
        function($value) use ($decimals) {
          return \number_format((float)$value, $decimals);
        }
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
        function($value) {
          return $value ? 'true' : 'false';
        }
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
     * @return \Papaya\Database\Statement\Prepared
     */
    public function addLimit($parameterName, $limit, $offset) {
      $this->addValue(
        $parameterName,
        NULL,
        function() use ($limit, $offset) {
          return $this->_databaseAccess->getSqlSource('LIMIT', [$limit, $offset]);
        }
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
          return $this->_databaseAccess->quoteIdentifier(
            $this->_databaseAccess->getTableName($tableName, $usePrefix)
          );
        }
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
