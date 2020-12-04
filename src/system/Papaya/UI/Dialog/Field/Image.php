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

namespace Papaya\UI\Dialog\Field {

  use Papaya\UI;
  use Papaya\XML;

  class Image extends UI\Dialog\Field {

    private $_image;
    /**
     * @var null
     */
    private $_width;
    /**
     * @var null
     */
    private $_height;

    /**
     * Create object and assign needed values
     *
     * @param string $image
     * @param int $width
     * @param int $height
     */
    public function __construct(
      $image, $width = NULL, $height = NULL
    ) {
      $this->_image = $image;
      $this->_width = $width;
      $this->_height = $height;
    }

    /**
     * Append image field to dialog xml dom
     *
     * @param XML\Element $parent
     */
    public function appendTo(XML\Element $parent) {
      $field = $this->_appendFieldTo($parent);
      $field->appendElement(
        'image',
        [
          'src' => $this->_image,
          'width' => $this->_width,
          'height' => $this->_height
        ]
      );
    }
  }
}
