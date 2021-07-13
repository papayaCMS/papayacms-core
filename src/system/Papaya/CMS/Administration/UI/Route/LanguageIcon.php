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
/** @noinspection PhpComposerExtensionStubsInspection */
namespace Papaya\CMS\Administration\UI\Route {

  use Papaya\CMS\Administration\UI\Path;
  use Papaya\BaseObject\Interactive;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Utility;
  use Papaya\XML\Document;

  class LanguageIcon extends Interactive implements Router\PathRoute {

    /**
     * @var string
     */
    private $_path;

    public function __construct($path) {
      $this->_path = $path;
    }

    /**
     * @param Router $router
     * @param null|Path $address
     * @param int $level
     * @return callable|Response|Router\Route\Error|true|null
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $parameters = $address->getRouteArray($level);
      $language = Utility\Arrays::get($parameters, 0, '');
      if ($this->validateIdentifier($language)) {
        $path = $this->_path.'/';
        $svgFileName = $path.'/'.$language.'.svg';
        if (\file_exists($svgFileName) && \is_readable($svgFileName)) {
          $response = new Response();
          $response->setContentType('image/svg+xml');
          $response->content(new Response\Content\File($svgFileName));
          return $response;
        }
        return new Router\Route\Error(
          \sprintf('Can not find language icon "%s"!', $language), 404
        );
      }
      return new Router\Route\Error(
        \sprintf('Invalid language for icon "%s"!', $language), 401
      );
    }

    /**
     * @param string $name
     * @return bool
     */
    private function validateIdentifier($name) {
      return (bool)\preg_match('(^[a-z](?:[a-z\\d-]*[a-z\\d])?$)D', $name);
    }
  }
}
