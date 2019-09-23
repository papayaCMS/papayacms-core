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
     * @return SQLSource
     */
    public function characterLength($text) {
      return new SQLSource('LENGTH('.$this->compileParameter($text).')');
    }

    /**
     * @param string|Parameter ...$arguments
     * @return SQLSource
     */
    public function concat(...$arguments) {
      $serialized = implode(
        ' || ',
        array_map(
          function ($argument) {
            return $this->compileParameter($argument);
          },
          $arguments
        )
      );
      return new SQLSource('('.$serialized.')');
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function length($text) {
      return new SQLSource('LENGTH('.$this->compileParameter($text).')');
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function like($text) {
      return new SQLSource('LIKE '.$this->compileParameter($text).' ESCAPE \'\\\\\'');
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return SQLSource
     */
    public function limit($limit, $offset = 0) {
      $limit = (int)$limit;
      $offset = (int)$offset;
      if ($limit > 0) {
        if ($offset > 0) {
          return new SQLSource(sprintf(' LIMIT %d,%d', $offset, $limit));
        }
        return new SQLSource(sprintf(' LIMIT %d', $limit));
      }
      return new SQLSource('');
    }

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return SQLSource
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
      return new SQLSource(
        sprintf(
          'LOCATE(%s, %s, %s)',
          $this->compileParameter($haystack),
          $this->compileParameter($needle),
          $this->compileParameter($offset)
        )
      );
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function lower($text) {
      return new SQLSource('LOWER('.$this->compileParameter($text).')');
    }

    /**
     * @return SQLSource
     */
    public function random() {
      return new SQLSource('RANDOM()');
    }

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @param string|Parameter $replaceWith
     * @return SQLSource
     */
    public function replace($haystack, $needle, $replaceWith) {
      return new SQLSource(
        sprintf(
          'REPLACE(%s, %s, %s)',
          $this->compileParameter($haystack),
          $this->compileParameter($needle),
          $this->compileParameter($replaceWith)
        )
      );
    }

    /**
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param int|Parameter $length
     * @return SQLSource
     */
    public function substring($haystack, $offset = 0, $length = 0) {
      if ($length instanceof SQLSource || $length > 0) {
        return new SQLSource(
          sprintf(
            'SUBSTRING(%s, %s, %s)',
            $this->compileParameter($haystack),
            $this->compileParameter($offset),
            $this->compileParameter($length)
          )
        );
      }
      return new SQLSource(
        sprintf(
          'SUBSTRING(%s, %s)',
          $this->compileParameter($haystack),
          $this->compileParameter($offset)
        )
      );
    }

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function upper($text) {
      return new SQLSource('UPPER('.$this->compileParameter($text).')');
    }
  }
}

