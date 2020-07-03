<?php

namespace Papaya\Database\Syntax {

  class MySQLSyntax extends AbstractSyntax {

    /**
     * @return string
     */
    public function getDialect() {
      return 'mysql';
    }

    /**
     * @param string|SQLSource ...$arguments
     * @return string
     */
    public function concat(...$arguments) {
      $serialized = implode(
        ', ',
        array_map(
          function($argument) {
            return $this->compileParameter($argument);
          },
          $arguments
        )
      );
      return 'CONCAT('.$serialized.')';
    }

    /*
     * @param string|SQLSource $values
     * @param string|SQLSource $separator
     *
     */
    public function groupConcat($values, $separator = ',') {
      return sprintf(
        'GROUP_CONCAT(%s SEPARATOR %s)',
        $this->compileParameter($values),
        $this->compileParameter($separator)
      );
    }

    /**
     * @param string|SQLSource $text
     * @return int
     */
    public function length($text) {
      return 'LENGTH('.$this->compileParameter($text).')';
    }

    /**
     * @param string|SQLSource $text
     * @return string
     */
    public function like($text) {
      return 'LIKE '.$this->compileParameter($text);
    }

    /**
     * @param string|SQLSource $haystack
     * @param string|SQLSource $needle
     * @param int|SQLSource $offset
     * @return string
     */
    public function locate($haystack, $needle, $offset = 0) {
      return sprintf(
        'LOCATE(%s, %s, %s)',
        $this->compileParameter($haystack),
        $this->compileParameter($needle),
        $this->compileParameter($offset)
      );
    }

    /**
     * @param string|SQLSource $text
     * @return string
     */
    public function lower($text) {
      return 'LOWER('.$this->compileParameter($text).')';
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
     * @return string
     */
    public function random() {
      return 'RANDOM()';
    }

    /**
     * @param string|SQLSource $haystack
     * @param int|SQLSource $offset
     * @param int|SQLSource $length
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
