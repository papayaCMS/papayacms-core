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
namespace Papaya\Router\Route {

  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Router\Route;
  use Papaya\Utility;

  /**
   * A list of routes that will be executed one after each other until
   * one of them returns an response.
   */
  class Group implements Route, \ArrayAccess {
    private $_routes = [];

    /**
     * Group constructor.
     *
     * @param callable|Route ...$routes
     */
    public function __construct(...$routes) {
      foreach ($routes as $route) {
        $this[] = $route;
      }
    }

    /**
     * @param Router $router
     * @param NULL|object $context
     * @param mixed[] $arguments
     * @return null|Response
     */
    public function __invoke(Router $router, $context = NULL, ...$arguments) {
      foreach ($this->_routes as $route) {
        if ($response = $route($router, $context, ...$arguments)) {
          return $response;
        }
      }
      return NULL;
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
      return isset($this->_routes[$offset]);
    }

    /**
     * @param int $offset
     * @param callable|Route $route
     */
    public function offsetSet($offset, $route): void {
      Utility\Constraints::assertCallable($route);
      if (NULL === $offset) {
        $this->_routes[] = $route;
      } else {
        Utility\Constraints::assertInteger($offset);
        $this->_routes[$offset] = $route;
        $this->_routes = \array_values($this->_routes);
      }
    }

    /**
     * @param int $offset
     * @return callable|Route $route
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
      return $this->_routes[$offset];
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void {
      unset($this->_routes[$offset]);
      $this->_routes = \array_values($this->_routes);
    }
  }
}
