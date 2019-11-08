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

  /**
   * Cache Response
   */
  class Gzip implements Router\Route {
    /**
     * @var callable
     */
    private $_route;

    /**
     * @param callable $route
     */
    public function __construct(callable $route) {
      $this->_route = $route;
    }

    /**
     * @param Router $router
     * @param NULL|object $context
     * @param mixed[] $arguments
     * @return null|Response
     */
    public function __invoke(Router $router, $context = NULL, ...$arguments) {
      $route = $this->_route;
      do {
        $route = $route($router, $context, ...$arguments);
        if ($route instanceof Response) {
          $helper = $route->helper();
          if ($helper->allowGzip() && !$helper->hasOutputBuffers() && $route->content()->length() > 0) {
            $response = $route->duplicate();
            ob_start();
            $route->content()->output();
            /** @noinspection PhpComposerExtensionStubsInspection */
            $response->content(
              new Response\Content\Text(\gzencode(\ob_get_clean()))
            );
            $response->headers()->set('Content-Encoding', 'gzip');
            return $response;
          }
          return $route;
        }
      } while (is_callable($route));
      return $route;
    }
  }
}
