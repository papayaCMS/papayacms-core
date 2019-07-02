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
     * @param string|Parameter ...$arguments
     * @return string
     */
    public function concat(...$arguments) {
      $serialized = implode(
        ', ',
        array_map(
          function($argument) {
            return $this->getParameter($argument);
          },
          $arguments
        )
      );
      return 'CONCAT('.$serialized.')';
    }

    /**
     * @param string|Parameter $text
     * @return int
     */
    public function length($text) {
      return 'LENGTH('.$this->getParameter($text).')';
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function like($text) {
      return 'LIKE '.$this->getParameter($text);
    }

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return string
     */
    public function locate($haystack, $needle, $offset = 0) {
      return sprintf(
        'LOCATE(%s, %s, %s)',
        $this->getParameter($haystack),
        $this->getParameter($needle),
        $this->getParameter($offset)
      );
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function lower($text) {
      return 'LOWER('.$this->getParameter($text).')';
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
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param int|Parameter $length
     * @return string
     */
    public function substring($haystack, $offset = 0, $length = 0) {
      return sprintf(
        'SUBSTRING(%s, %s, %s)',
        $this->getParameter($haystack),
        $this->getParameter($offset),
        $this->getParameter($length)
      );
    }

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function upper($text) {
      return 'UPPER('.$this->getParameter($text).')';
    }
  }
}
