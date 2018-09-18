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

namespace Papaya\Media\File;

class Properties extends Info {
  private $_fetchers;

  protected function fetchProperties() {
    $file = $this->getFile();
    if (\file_exists($file) && \is_file($file) && \is_readable($file)) {
      $properties = [];
      foreach ($this->fetchers() as $fetcher) {
        if ($fetcher->isSupported($properties)) {
          /** @noinspection SlowArrayOperationsInLoopInspection */
          $properties = \array_merge($properties, \iterator_to_array($fetcher));
        }
      }
      return $properties;
    }
    return [
      'is_valid' => FALSE
    ];
  }

  public function fetchers() {
    $fetchers = \func_get_args();
    if (\count($fetchers) > 0) {
      $this->_fetchers = $fetchers;
    } elseif (NULL === $this->_fetchers) {
      $file = $this->getFile();
      $originalName = $this->getOriginalFileName();
      $this->_fetchers = [
        new Info\Basic($file, $originalName),
        new Info\Mimetype($file, $originalName),
        new Info\Image($file, $originalName),
        new Info\SVG($file, $originalName),
      ];
    }
    return $this->_fetchers;
  }
}
