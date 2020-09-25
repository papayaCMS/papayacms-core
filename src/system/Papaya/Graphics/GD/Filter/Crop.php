<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Graphics\GD\Filter {

  use Papaya\Graphics\BoundingRectangle;
  use Papaya\Graphics\GD\GDFilter;

  class Crop implements GDFilter {

    private $_boundary;

    public function __construct(BoundingRectangle $boundary) {
      $this->_boundary = $boundary;
    }

    public function applyTo(&$imageResource) {
      imagecrop(
        $imageResource,
        [
          'x' => $this->_boundary->getLeft(),
          'y' => $this->_boundary->getTop(),
          'width' => $this->_boundary->getWidth(),
          'height' => $this->_boundary->getHeight(),
        ]
      );
    }
  }
}
