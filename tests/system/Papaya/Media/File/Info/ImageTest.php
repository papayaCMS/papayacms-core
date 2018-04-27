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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaMediaFileInfoImageTest extends PapayaTestCase {

  public function testFetchInfoFromPng() {
    $info = new PapayaMediaFileInfoImage(__DIR__.'/TestData/20x20.png');
    $this->assertEquals(
      [
        'is_valid' => TRUE,
        'mimetype' => 'image/png',
        'imagetype' => IMAGETYPE_PNG,
        'width' => 20,
        'height' => 20,
        'bits' => 8,
        'channels' => 0,
        'extension' => 'png'
      ],
      iterator_to_array($info, TRUE)
    );
  }
}
