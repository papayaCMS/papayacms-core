<?php

namespace Papaya\Database {

  use Papaya\Database\Syntax\Identifier;
  use Papaya\Database\Syntax\Parameter;
  use Papaya\Database\Syntax\Placeholder;

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
     * @return string
     */
    public function characterLength($text);

    /**
     * @param string|Parameter ...$arguments
     * @return string
     */
    public function concat(...$arguments);

    /**
     * @param string|Parameter $text
     * @return int
     */
    public function length($text);

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function like($text);

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset = 0);

    /**
     * @param string|Parameter haystack
     * @param string|Parameter $needle
     * @param int|Parameter $offset
     * @return string
     */
    public function locate($haystack, $needle, $offset = 0);

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function lower($text);

    /**
     * @return string
     */
    public function random();

    /**
     * @param string|Parameter$haystack
     * @param string|Parameter$needle
     * @param string|Parameter$thread
     * @return string
     */
    public function replace($haystack, $needle, $thread);

    /**
     * @param string|Parameter $haystack
     * @param int|Parameter $offset
     * @param null|int|Parameter $length
     * @return string
     */
    public function substring($haystack, $offset, $length = NULL);

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @return string
     */
    public function substringCount($haystack, $needle);

    /**
     * @param string|Parameter $text
     * @return string
     */
    public function upper($text);
  }
}
