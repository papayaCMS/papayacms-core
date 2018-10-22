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

  class Choice implements Route, \ArrayAccess {
    const EXECUTE_ALWAYS = 'always';

    const EXECUTE_ON_SUCCESS = 'success';

    const EXECUTE_ON_FAILURE = 'failure';

    private $_before = [];

    private $_routes = [];

    private $_after = [];

    public function __invoke(array $path) {
      $command = \array_shift($path);
      if (!isset($this[$command])) {
        return;
      }
      $success = TRUE;
      foreach ($this->_before as list($callback, $filter)) {
        if ($success || self::EXECUTE_ALWAYS === $filter || self::EXECUTE_ON_FAILURE === $filter) {
          if (!$callback()) {
            $success = FALSE;
          }
        }
      }
      if ($success) {
        $this[$command]($path);
      }
      foreach ($this->_after as list($callback, $filter)) {
        if ($success || self::EXECUTE_ALWAYS === $filter || self::EXECUTE_ON_FAILURE === $filter) {
          if (!$callback()) {
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

    public function offsetExists($command) {
      return isset($this->_routes[$command]);
    }

    public function offsetSet($command, $route) {
      Utility\Constraints::assertString($command);
      Utility\Constraints::assertCallable($route);
      $this->_routes[$command] = $route;
    }

    public function offsetGet($command) {
      return $this->_routes[$command];
    }

    public function offsetUnset($command) {
      unset($this->_routes[$command]);
    }
  }
}
