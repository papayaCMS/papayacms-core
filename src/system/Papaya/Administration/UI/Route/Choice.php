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

  use Papaya\Administration\UI;
  use Papaya\Administration\UI\Route;
  use Papaya\Utility;

  /**
   * Select and execute route by path.
   */
  class Choice implements Route, \ArrayAccess {
    /**
     * @var array
     */
    private $_routes = [];

    /**
     * @var null
     */
    private $_defaultChoice;

    /**
     * @var int
     */
    private $_offset;

    /**
     * @param array $choices
     * @param null|string $defaultChoice default choice if route path is empty
     * @param int $offset offset while fetching current route path
     */
    public function __construct(array $choices = [], $defaultChoice = NULL, $offset = 0) {
      $this->_offset = (int)$offset;
      foreach ($choices as $path => $route) {
        $this[$path] = $route;
      }
      if (NULL !== $defaultChoice) {
        $this->_defaultChoice = $defaultChoice;
      }
    }

    /**
     * @param UI $ui
     * @param Address $path
     * @param int $level
     * @return null|TRUE|\Papaya\Response|callable
     */
    public function __invoke(UI $ui, Address $path, $level = 0) {
      $command = $path->getRoute($level, $this->_offset) ?: $this->_defaultChoice;
      if (!isset($this[$command])) {
        return NULL;
      }
      return $this[$command]($ui, $path, ++$level);
    }

    /**
     * @param string $command
     * @return bool
     */
    public function offsetExists($command) {
      return isset($this->_routes[$command]);
    }

    /**
     * @param string $command
     * @return callable|Route
     */
    public function offsetGet($command) {
      return $this->_routes[$command];
    }

    /**
     * @param string $command
     * @param callable $route
     */
    public function offsetSet($command, $route) {
      Utility\Constraints::assertString($command);
      Utility\Constraints::assertCallable($route);
      $this->_routes[$command] = $route;
    }

    /**
     * @param string $command
     */
    public function offsetUnset($command) {
      unset($this->_routes[$command]);
    }
  }
}
