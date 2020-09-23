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

namespace Papaya\Media {

  use Papaya\Graphics\GD\Filter\CopyResampled;
  use Papaya\Graphics\GD\GDLibrary;
  use Papaya\Media\Thumbnail\Calculation;

  class Thumbnail {
    /**
     * @var string|resource
     */
    private $_source;

    /**
     * @var GDLibrary
     */
    private $_gd;
    /**
     * @var Calculation
     */
    private $_calculation;

    public function __construct($source, Calculation $calculation) {
      $this->_source = $source;
      $this->_calculation = $calculation;
    }

    public function gd(GDLibrary $gd = NULL) {
      if (isset($gd)) {
        $this->_gd = $gd;
      } elseif (NULL === $this->_gd) {
        $this->_gd = new GDLibrary();
      }
      return $this->_gd;
    }

    public function create() {
      $gd = $this->gd();
      if ($source = $gd->load($this->_source)) {
        $calculation = $this->_calculation;
        $destination = $gd->create(...$calculation->getTargetSize());
        $destination->filter(
          new CopyResampled(
            $source, $calculation->getSource(), $calculation->getDestination()
          )
        );
        return $destination;
      }
      return NULL;
    }
  }
}
