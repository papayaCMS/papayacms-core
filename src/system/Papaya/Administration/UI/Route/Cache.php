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

  use Papaya\Application\Access;
  use Papaya\Response;
  use Papaya\Router;

  /**
   * Cache Response
   */
  class Cache implements Router\PathRoute, Access {
    use Access\Aggregation;

    const CACHE_PRIVATE = Response::CACHE_PRIVATE;
    const CACHE_PUBLIC = Response::CACHE_PUBLIC;

    /**
     * @var callable
     */
    private $_route;

    /**
     * @var string
     */
    private $_group = 'administration';

    /**
     * If empty the current papaya revision or 'dev' will be used
     *
     * @var string
     */
    private $_element = '';

    /**
     * @var mixed
     */
    private $_identifier;

    /**
     * @var string
     */
    private $_cacheMode;

    /**
     * @var int
     */
    private $_cacheTime;

    /**
     * @var \Papaya\Cache\Service
     */
    private $_cacheService;

    /**
     * @param callable $route
     * @param mixed $identifier
     * @param int $seconds
     * @param string $mode
     */
    public function __construct(callable $route, $identifier, $seconds, $mode = self::CACHE_PRIVATE) {
      $this->_identifier = $identifier;
      $this->_cacheMode = (string)$mode;
      $this->_cacheTime = (int)$seconds;
      $this->_route = $route;
    }

    public function getCacheIdentifier($routePath, $compress = FALSE) {
      $result = \str_replace('/', '.', $routePath);
      $result .= '.'.\md5(\serialize($this->_identifier));
      $result .= ($compress ? '.gz' : '');
      return $result;
    }

    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     * @return null|Response
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $application = $this->papaya($router->papaya());
      $route = $this->_route;
      if ($this->_cacheTime < 1) {
        return $route($router, $address, $level);
      }
      $cacheElement = '' !== \trim($this->_element)
        ? $this->_element
        : $application->options->get('PAPAYA_VERSION_STRING', 'dev', new \Papaya\Filter\NotEmpty());
      $cacheId = $this->getCacheIdentifier($address->getRouteString(-1));
      $data = NULL;
      $lastModified = $this->cache()->created($this->_group, $cacheElement, $cacheId, $this->_cacheTime);
      if ($application->request->validateBrowserCache($cacheId, $lastModified)) {
        $response = new Response();
        $response->setStatus(304);
        $response->setCache($this->_cacheMode, $this->_cacheTime, $lastModified);
        $response->headers()->set('Etag', $cacheId);
        return $response;
      }
      if ($data = $this->cache()->read($this->_group, $cacheElement, $cacheId, $this->_cacheTime)) {
        $data = \unserialize($data);
      }
      if ($data && isset($data['type'], $data['content'])) {
        $response = new Response();
        $response->setCache($this->_cacheMode, $this->_cacheTime, $lastModified);
        $response->headers()->set('Etag', $cacheId);
        $response->setContentType($data['type']);
        $response->content(new Response\Content\Text($data['content']));
        return $response;
      }
      if (($response = $route($router, $address, $level)) instanceof Response) {
        /** @var Response $response */
        if ($this->_cacheTime > 0 && 200 === $response->getStatus()) {
          \ob_start();
          $response->content()->output();
          $content = \ob_get_clean();
          $this->cache()->write(
            $this->_group,
            $cacheElement,
            $cacheId,
            \serialize(
              ['type' => $response->getContentType(), 'content' => $content]
            ),
            $this->_cacheTime
          );
          $response->headers()->set('Etag', $cacheId);
          $response->setCache($this->_cacheMode, $this->_cacheTime);
        }
        return $response;
      }
      return NULL;
    }

    /**
     * Getter/setter for cache service object
     *
     * @param \Papaya\Cache\Service $service
     *
     * @return \Papaya\Cache\Service
     */
    public function cache(\Papaya\Cache\Service $service = NULL) {
      if (NULL !== $service) {
        $this->_cacheService = $service;
      } elseif (NULL === $this->_cacheService) {
        /* @noinspection PhpParamsInspection */
        $this->_cacheService = \Papaya\Cache::get(
          \Papaya\Cache::OUTPUT, $this->papaya()->options
        );
      }
      return $this->_cacheService;
    }
  }
}
