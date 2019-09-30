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
namespace Papaya {

  use Papaya\Application\Access as ApplicationAccess;

  class Router implements ApplicationAccess {
    use ApplicationAccess\Aggregation;

    /**
     * @var callable|Router\Route
     */
    private $_route;

    /**
     * UI constructor.
     *
     * @param Application $application
     * @param callable|null $route
     */
    public function __construct(
      Application $application, callable $route = NULL
    ) {
      $this->papaya($application);
      $this->_route = $route;
    }

    /**
     * Initialize application and options and execute routes depending on the URL.
     *
     * Possible return values for routes:
     *   \Papaya\Response - returned from this method
     *   TRUE - request was handled, do not execute other routes
     *   NULL - not handled, continue route execution
     *   callable - new route, execute
     *
     * @return null|Response
     */
    public function execute() {
      $context = $this->getRouteContext();
      $route = $this->route();
      do {
        $route = $route($this, $context);
        if ($route instanceof Response) {
          return $route;
        }
      } while (\is_callable($route));
      return NULL;
    }

    public function getRouteContext() {
      return NULL;
    }

    /**
     * @param callable|NULL|Router\Route $route
     * @return callable|Router\Route
     */
    public function route(callable $route = NULL) {
      if (NULL !== $route) {
        $this->_route = $route;
      } elseif (NULL === $this->_route) {
        $this->_route = $this->createRoute();
      }
      return $this->_route;
    }

    /**
     * @return callable|Router\Route
     */
    protected function createRoute() {
      throw new \LogicException('No route defined.');
    }
  }
}
