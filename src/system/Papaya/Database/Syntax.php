<?php

namespace Papaya\Database {

  use Papaya\Database\Syntax\Identifier;
  use Papaya\Database\Syntax\Parameter;
  use Papaya\Database\Syntax\Placeholder;
  use Papaya\Database\Syntax\SQLSource;

  interface Syntax {

    /**
     * @return string
     */
    public function getDialect();

    /**
     * @param string $name
     * @return Identifier
     */
    public function identifier($name);

    /**
     * @param string $name
     * @return Placeholder
     */
    public function placeholder($name);

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function characterLength($text);

    /**
     * @param string|Parameter ...$arguments
     * @return SQLSource
     */
    public function concat(...$arguments);

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function length($text);

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function like($text);

    /**
     * @param int $limit
     * @param int $offset
     * @return SQLSource
     */
    public function limit($limit, $offset = 0);

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return SQLSource
     */
    public function locate($haystack, $needle, $offset = 0);

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function lower($text);

    /**
     * @return SQLSource
     */
    public function random();

    /**
     * @param string|Parameter$haystack
     * @param string|Parameter$needle
     * @param string|Parameter$thread
     * @return SQLSource
     */
    public function replace($haystack, $needle, $thread);

    /**
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param null|int|Parameter $length
     * @return SQLSource
     */
    public function substring($haystack, $offset, $length = NULL);

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @return SQLSource
     */
    public function substringCount($haystack, $needle);

    /**
     * @param string|Parameter $text
     * @return SQLSource
     */
    public function upper($text);
  }
}
