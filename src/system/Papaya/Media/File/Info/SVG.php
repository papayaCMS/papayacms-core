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
namespace Papaya\Media\File\Info;

class SVG extends \Papaya\Media\File\Info {
  public $forceDOM = FALSE;

  const XMLNS_SVG = 'http://www.w3.org/2000/svg';

  public function isSupported(array $fileProperties = []) {
    $extension = \strrchr($this->getOriginalFileName(), '.');
    return (
      (
        isset($fileProperties['mimetype']) &&
        (
          'image/svg' === $fileProperties['mimetype'] ||
          'image/svg+xml' === $fileProperties['mimetype']
        )
      ) ||
      (
        '.svg' === $extension
      )
    );
  }

  protected function fetchProperties() {
    $properties = [
      'is_valid' => FALSE,
      'width' => 0,
      'height' => 0
    ];
    if (!$this->forceDOM && \class_exists('XMLReader')) {
      $reader = new \XMLReader();
      if (@$reader->open($this->getFile())) {
        $found = @$reader->read();
        while ($found && !('svg' === $reader->localName && self::XMLNS_SVG === $reader->namespaceURI)) {
          $found = $reader->next('svg');
        }
        if ($found) {
          $properties['is_valid'] = TRUE;
          $properties['width'] = (int)$reader->getAttribute('width');
          $properties['height'] = (int)$reader->getAttribute('height');
        }
      }
    } else {
      $document = new \Papaya\XML\Document();
      if (@$document->load($this->getFile())) {
        $node = $document->documentElement;
        if ($node && 'svg' === $node->localName && self::XMLNS_SVG === $node->namespaceURI) {
          $properties['is_valid'] = TRUE;
          $properties['width'] = (int)$node->getAttribute('width');
          $properties['height'] = (int)$node->getAttribute('height');
        }
      }
    }
    return $properties;
  }
}
