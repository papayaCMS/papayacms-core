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
namespace Papaya\Administration\UI\Route {

  use Papaya\Administration\UI\Route;
  use Papaya\Utility;

  class Group implements Route, \ArrayAccess {
    const EXECUTE_ALWAYS = 'always';

    const EXECUTE_ON_SUCCESS = 'success';

    const EXECUTE_ON_FAILURE = 'failure';

    private $_before = [];

    private $_routes = [];

    private $_after = [];

    public function __construct(...$routes) {
      foreach ($routes as $route) {
        $this[] = $route;
      }
    }

    public function __invoke(\Papaya\Administration\UI $ui, Address $path, $level = 0) {
      $success = TRUE;
      foreach ($this->_before as list($callback, $filter)) {
        if ($success || self::EXECUTE_ALWAYS === $filter || self::EXECUTE_ON_FAILURE === $filter) {
          if (!$callback($ui)) {
            $success = FALSE;
          }
        }
      }
      if ($success) {
        foreach ($this->_routes as $route) {
          $route($ui, $path, $level);
        }
      }
      foreach ($this->_after as list($callback, $filter)) {
        if ($success || self::EXECUTE_ALWAYS === $filter || self::EXECUTE_ON_FAILURE === $filter) {
          if (!$callback($ui)) {
            $success = FALSE;
          }
        }
      }
    }

    public function before(callable $callback, $executionFilter = self::EXECUTE_ON_SUCCESS) {
      $this->_before[] = [$callback, $executionFilter];
    }

    public function after(callable $callback, $executionFilter = self::EXECUTE_ON_SUCCESS) {
      $this->_after[] = [$callback, $executionFilter];
    }

    public function offsetExists($offset) {
      return isset($this->_routes[$offset]);
    }

    public function offsetSet($offset, $route) {
      Utility\Constraints::assertCallable($route);
      if (NULL === $offset) {
        $this->_routes[] = $route;
      } else {
        Utility\Constraints::assertInteger($offset);
        $this->_routes[$offset] = $route;
        $this->_routes = \array_values($this->_routes);
      }
    }

    public function offsetGet($offset) {
      return $this->_routes[$offset];
    }

    public function offsetUnset($offset) {
      unset($this->_routes[$offset]);
      $this->_routes = \array_values($this->_routes);
    }
  }
}
