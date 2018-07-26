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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaResponseContentStringTest extends PapayaTestCase {

  /**
  * @covers \PapayaResponseContentString::__construct
  */
  public function testConstructor() {
    $content = new \PapayaResponseContentString('sample');
    $this->assertAttributeEquals(
      'sample', '_content', $content
    );
  }

  /**
  * @covers \PapayaResponseContentString::length
  */
  public function testLength() {
    $content = new \PapayaResponseContentString('sample');
    $this->assertEquals(6, $content->length());
  }

  /**
  * @covers \PapayaResponseContentString::output
  */
  public function testOutput() {
    $content = new \PapayaResponseContentString('sample');
    ob_start();
    $content->output();
    $this->assertEquals('sample', ob_get_clean());
  }

  /**
  * @covers \PapayaResponseContentString::__toString
  */
  public function testMagicMethodToString() {
    $content = new \PapayaResponseContentString('sample');
    $this->assertEquals('sample', (string)$content);
  }

}
