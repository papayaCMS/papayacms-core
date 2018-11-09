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
namespace Papaya\Administration\UI {

  /**
   * Parse URL into a route address
   *
   * Everything after the $basePath up to the first ? or # will be split
   * by / and .
   *
   *   'administration.settings' -> ['administration', 'settings']
   *   'css/main' -> ['css', 'main']
   *
   * @package Papaya\Router\Route
   */
  class Address extends \Papaya\Router\Address {
    /**
     * @var \Papaya\URL\Current
     */
    private $_url;

    /**
     * @var null|array
     */
    private $_parts;

    /**
     * @var null|array
     */
    private $_separators;

    /**
     * @var string
     */
    private $_basePath;

    /**
     * Address constructor.
     *
     * @param string $basePath
     * @param \Papaya\URL|null $url
     */
    public function __construct($basePath, \Papaya\URL $url = NULL) {
      $this->_basePath = $basePath;
      if (NULL === $url) {
        $this->_url = new \Papaya\URL\Current();
      }
    }

    /**
     * Lazy parsing for the route path
     *
     * @param int $offset
     * @return array|null
     */
    public function getRouteArray($offset = 0) {
      if (NULL === $this->_parts) {
        $pattern = '('.\preg_quote($this->_basePath, '(').'/(?<path>[^?#]*))';
        if (\preg_match($pattern, $this->_url->path, $matches)) {
          $values = \preg_split('(([/.]))', $matches['path'], -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
          $this->_parts = \array_values(
            \array_filter(
              $values,
              function($key) {
                return !($key % 2);
              },
              ARRAY_FILTER_USE_KEY
            )
          );
          $this->_separators = \array_values(
            \array_filter(
              $values,
              function($key) {
                return $key % 2;
              },
              ARRAY_FILTER_USE_KEY
            )
          );
        } else {
          $this->_parts = [];
          $this->_separators = [];
        }
      }
      return \array_slice($this->_parts, $offset);
    }

    /**
     * @param $offset
     * @return mixed|string
     */
    public function getSeparator($offset) {
      return isset($this->_separators[$offset]) ? $this->_separators[$offset] : '.';
    }
  }
}
