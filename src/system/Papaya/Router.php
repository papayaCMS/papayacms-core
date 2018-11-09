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

  abstract class Router implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var callable|Router\Route
     */
    private $_route;

    /**
     * UI constructor.
     *
     * @param string $path
     * @param \Papaya\Application $application
     */
    public function __construct($path, Application $application) {
      $this->_path = \str_replace(DIRECTORY_SEPARATOR, '/', $path);
      $this->papaya($application);
    }

    /**
     * @return string Local path to administration directory
     */
    public function getLocalPath() {
      return $this->_path;
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
     * @return null|\Papaya\Response
     */
    public function execute() {
      $address = $this->address();
      $route = $this->route();
      do {
        $route = $route($this, $address);
        if ($route instanceof Response) {
          return $route;
        }
      } while (\is_callable($route));
      return NULL;
    }

    /**
     * @param Router\Address|null $address
     * @return Router\Address
     */
    abstract public function address(Router\Address $address = NULL);

    /**
     * @param callable|null|Router\Route $route
     * @return mixed
     */
    abstract public function route(callable $route = NULL);
  }
}
