<?php

namespace Papaya\Database\Syntax {

  class SQLiteSyntax extends AbstractSyntax {

    private $_callbacks;

    /**
     * @return string
     */
    public function getDialect() {
      return 'sqlite';
    }

    /**
     * @param string|Parameter $text
     * @return int
     */
    public function characterLength($text) {
      return 'LENGTH('.$this->compileParameter($text).')';
    }

    /**
     * @param string|Parameter ...$arguments
     * @return string
     */
    public function concat(...$arguments) {
      $serialized = implode(
        ' || ',
        array_map(
          function($argument) {
            return $this->compileParameter($argument);
          },
          $arguments
        )
      );
      return '('.$serialized.')';
    }

    /**
     * @param string|Parameter $text
     * @return int
     */
    public function length($text) {
      return 'LENGTH('.$this->compileParameter($text).')';
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function like($text) {
      return 'LIKE '.$this->compileParameter($text).' ESCAPE \'\\\\\'';
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset = 0) {
      $limit = (int)$limit;
      $offset = (int)$offset;
      if ($limit > 0) {
        if ($offset > 0) {
          return sprintf(' LIMIT %d,%d', $offset, $limit);
        }
        return sprintf(' LIMIT %d', $limit);
      }
      return '';
    }

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return string
     */
    public function locate($haystack, $needle, $offset = 0) {
      if (!isset($this->_callbacks['LOCATE'])) {
        $this->_connection->registerFunction(
          'LOCATE',
          static function ($needle, $haystack, $offset = 0) {
            $pos = strpos($haystack, $needle, $offset);
            if ($pos !== FALSE) {
              return ++$pos;
            }
            return 0;
          }
        );
        $this->_callbacks['LOCATE'] = TRUE;
      }
      return sprintf(
        'LOCATE(%s, %s, %s)',
        $this->compileParameter($haystack),
        $this->compileParameter($needle),
        $this->compileParameter($offset)
      );
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function lower($text) {
      return 'LOWER('.$this->compileParameter($text).')';
    }

    /**
     * @return string
     */
    public function random() {
      return 'RANDOM()';
    }

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @param string|Parameter $replaceWith
     * @return string
     */
    public function replace($haystack, $needle, $replaceWith) {
      return sprintf(
        'REPLACE(%s, %s, %s)',
        $this->compileParameter($haystack),
        $this->compileParameter($needle),
        $this->compileParameter($replaceWith)
      );
    }

    /**
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param int|Parameter $length
     * @return string
     */
    public function substring($haystack, $offset = 0, $length = 0) {
      if ($length instanceof SQLSource || $length > 0) {
        return sprintf(
          'SUBSTRING(%s, %s, %s)',
          $this->compileParameter($haystack),
          $this->compileParameter($offset),
          $this->compileParameter($length)
        );
      }
      return sprintf(
        'SUBSTRING(%s, %s)',
        $this->compileParameter($haystack),
        $this->compileParameter($offset)
      );
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function upper($text) {
      return 'UPPER('.$this->compileParameter($text).')';
    }
  }
}

