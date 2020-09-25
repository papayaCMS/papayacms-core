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

namespace Papaya\Media\Thumbnail\Calculation {

  use Papaya\Test\TestCase;

  class CoverCropTest extends TestCase {

    /**
     * @testWith
     *   [[10, 10], [1, 1], [0,0], [10,10]]
     *   [[10, 10], [5, 5], [0,0], [10,10]]
     *   [[10, 10], [5, 1], [0,4], [10,2]]
     *   [[10, 10], [3, 5], [2,0], [6,10]]
     *   [[10, 5], [3, 5], [3,0], [3,5]]
     *   [[5, 10], [5, 3], [0,3], [5,3]]
     *   [[5, 10], [20, 20], [0,2], [5,5]]
     */
    public function testCalculation($imageSize, $thumbnailSize, $expectedOffset, $expectedSize) {
      $calculation = new CoverCrop($imageSize[0], $imageSize[1], $thumbnailSize[0], $thumbnailSize[1]);
      $this->assertEquals($thumbnailSize, $calculation->getTargetSize());
      $this->assertEquals($expectedOffset, $calculation->getSource()->getOffset());
      $this->assertEquals($expectedSize, $calculation->getSource()->getSize());
      $this->assertEquals([0,0], $calculation->getDestination()->getOffset());
      $this->assertEquals($thumbnailSize, $calculation->getDestination()->getSize());
    }

  }

}
