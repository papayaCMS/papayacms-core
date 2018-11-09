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
     * @param Router\Address $address
     * @param int $level
     * @return callable|null|\Papaya\Response|true
     */
    public function __invoke(Router $router, Router\Address $address, $level = 0) {
      $route = $this->_route;
      do {
        $route = $route($router, $address, $level);
        if ($route instanceof Response) {
          if ($this->canUseOutputCompression() && $route->content()->length() > 0) {
            $response = clone $route;
            \ob_start();
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
      } while (\is_callable($route));
      return $route;
    }

    /**
     * @return bool
     */
    private function canUseOutputCompression() {
      if (
        \function_exists('ob_gzhandler') &&
        TRUE !== (bool)@\ini_get('zlib.output_compression') &&
        !\headers_sent()
      ) {
        $status = \ob_get_status(TRUE);
        \array_pop($status);
        return 0 === \count(
          \array_filter(
            $status,
            function($status) {
              return !isset($status['buffer_used']) || 0 !== $status['buffer_used'];
            }
          )
        );
      }
      return FALSE;
    }
  }
}
