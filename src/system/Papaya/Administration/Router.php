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
namespace Papaya\Administration {

  use Papaya\Administration\UI\Route;
  use Papaya\Application;
  use Papaya\Response;
  use Papaya\Template;

  abstract class Router implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * @var Template
     */
    private $_template;

    /**
     * @var \Papaya\Theme\Handler
     */
    private $_themeHandler;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var callable
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
      $application = $this->papaya();
      $address = new Route\Address($application->options->get('PAPAYA_PATH_ADMIN', ''));
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
     * @param callable|null $route
     * @return mixed
     */
    abstract public function route(callable $route = NULL);
  }
}
