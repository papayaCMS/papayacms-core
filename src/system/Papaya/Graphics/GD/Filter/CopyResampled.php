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
  use Papaya\Graphics\GD\GDImage;

  class CopyResampled implements GDFilter {

    /**
     * @var GDImage
     */
    private $_source;
    /**
     * @var BoundingRectangle
     */
    private $_sourceBoundary;
    /**
     * @var BoundingRectangle
     */
    private $_targetBoundary;

    public function __construct(GDImage $source, BoundingRectangle $sourceBoundary, BoundingRectangle $targetBoundary) {
      $this->_source = $source;
      $this->_sourceBoundary = $sourceBoundary;
      $this->_targetBoundary = $targetBoundary;
    }

    public function applyTo(&$imageResource) {
      return imagecopyresampled(
        $imageResource,
        $this->_source->getResource(),
        $this->_sourceBoundary->getLeft(),
        $this->_sourceBoundary->getTop(),
        $this->_sourceBoundary->getWidth(),
        $this->_sourceBoundary->getHeight(),
        $this->_targetBoundary->getLeft(),
        $this->_targetBoundary->getTop(),
        $this->_targetBoundary->getWidth(),
        $this->_targetBoundary->getHeight()
      );
    }
  }
}
