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
namespace Papaya\Administration\UI\Route {

  use Papaya\BaseObject\Interactive;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Utility;
  use Papaya\XML\Document;

  class Icon extends Interactive implements Router\Route {

    /**
     * @var array
     */
    private static $_sizes = [48, 22, 16];

    /**
     * @var string
     */
    private $_path;

    public function __construct($path) {
      $this->_path = $path;
    }

    public function __invoke(Router $router, Router\Address $address, $level = 0) {
      $size = $this->parameters()->get(
        'size', 16, new \Papaya\Filter\ArrayElement(self::$_sizes)
      );
      $parameters = $address->getRouteArray($level);
      $category = Utility\Arrays::get($parameters, 0, '');
      $name = Utility\Arrays::get($parameters, 1, '');
      $modifiers = \explode(',', Utility\Arrays::get($parameters, 2, ''));
      if ($this->validateIdentifier($category) && $this->validateIdentifier($name)) {
        return $this->getIcon($category, $name, $size, $modifiers);
      }
      return new Router\Route\Error(
        \sprintf('Invalid icon "%s"!', $address->getRouteString(-1, $level)), 404
      );
    }

    /**
     * Create a response for the requested icon, this method will call itself with a smaller
     * size if the icon file is not found.
     *
     * @param string $category
     * @param string $name
     * @param int $size
     * @param array $modifiers
     * @return \Papaya\Response|\Papaya\Router\Route\Error
     */
    private function getIcon($category, $name, $size, array $modifiers = []) {
      $path = $this->_path.'/'.$size.'x'.$size;
      $svgFileName = $path.'/'.$category.'/'.$name.'.svg';
      if (\file_exists($svgFileName) && \is_readable($svgFileName)) {
        $response = new Response();
        $response->setContentType('image/svg+xml');
        if ($modifiers) {
          $svg = new Document();
          $svg->load($svgFileName);
          foreach ($modifiers as $modifier) {
            $this->applyModifierToSVG($svg, $modifier, $path);
          }
          $response->content(new Response\Content\Text($svg->saveXML()));
        } else {
          $response->content(new Response\Content\File($svgFileName));
        }
        return $response;
      }
      $pngFileName = $path.'/'.$category.'/'.$name.'.png';
      if (\file_exists($pngFileName) && \is_readable($pngFileName)) {
        $response = new Response();
        $response->setContentType('image/png');
        if ($modifiers) {
          $image = \imagecreatefrompng($pngFileName);
          foreach ($modifiers as $modifier) {
            $this->applyModifierToImage($image, $modifier, $path);
          }
          \ob_start();
          \imagepng($image);
          $response->content(new Response\Content\Text(\ob_get_clean()));
          \imagedestroy($image);
        } else {
          $response->content(new Response\Content\File($pngFileName));
        }
        return $response;
      }
      foreach (self::$_sizes as $possibleSize) {
        if ($possibleSize < $size) {
          return $this->getIcon($category, $name, $possibleSize, $modifiers);
        }
      }
      return new Router\Route\Error(
        \sprintf('Unknown icon "%s.%s"!', $category, $name), 404
      );
    }

    /**
     * Add modifier images to the icon (add, remove, ...)
     * "disabled" will wrap all current image data, add a grayscale filter and set the opacity to 75%
     *
     * @param \Papaya\XML\Document $document
     * @param string $name
     * @param string $path
     */
    private function applyModifierToSVG(Document $document, $name, $path) {
      $svg = $document->documentElement;
      $document->registerNamespace('#default', 'http://www.w3.org/2000/svg');
      $document->registerNamespace('svg', 'http://www.w3.org/2000/svg');
      if ('disabled' === $name) {
        $group = $svg
          ->appendElement('g', ['opacity' => '0.75', 'filter' => 'url(#disabled)']);
        foreach ($document->xpath()->evaluate('svg:*[not(self::svg:defs)]', $svg) as $node) {
          $group->appendChild($node);
        }
        $svg
          ->appendElement('defs')
          ->appendElement('filter', ['id' => 'disabled'])
          ->appendElement(
            'feColorMatrix',
            [
              'type' => 'matrix',
              'values' => '0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'
            ]
          );
        return;
      }
      $modifierFile = $path.'/emblems/'.$name.'.svg';
      if ($this->validateIdentifier($name) && \file_exists($modifierFile) && \is_readable($modifierFile)) {
        $modifierDocument = new Document();
        $modifierDocument->load($modifierFile);
        $modifierDocument->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        foreach ($modifierDocument->xpath()->evaluate('(/svg:svg/svg:*)') as $node) {
          $svg->appendChild(
            $document->importNode($node, TRUE)
          );
        }
      }
    }

    /**
     * Add modifier images to the icon (add, remove, ...)
     * "disabled" will apply a grayscale filter and change the opacity to 75%
     *
     * @param resource $image
     * @param string $name
     * @param string $path
     */
    private function applyModifierToImage($image, $name, $path) {
      $modifierFile = $path.'/emblems/'.$name.'.png';
      if ('disabled' === $name) {
        $opacity = 0.75;
        \imagealphablending($image, false);
        \imagesavealpha($image, true);
        \imagefilter($image, IMG_FILTER_GRAYSCALE);
        \imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * (1 - $opacity));
        return;
      }
      if ($this->validateIdentifier($name) && \file_exists($modifierFile) && \is_readable($modifierFile)) {
        $modifierImage = \imagecreatefrompng($modifierFile);
        \imagecopy(
          $image, $modifierImage, 0, 0, 0, 0, \imagesx($modifierImage), \imagesy($modifierImage)
        );
        \imagedestroy($modifierImage);
      }
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
