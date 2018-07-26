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

class PapayaResponseContentFileTest extends PapayaTestCase {

  /**
  * @covers \PapayaResponseContentFile::__construct
  */
  public function testConstructor() {
    $content = new \PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertStringEndsWith(
      '/TestData/data.txt', $this->readAttribute($content, '_filename')
    );
  }

  /**
  * @covers \PapayaResponseContentFile::length
  */
  public function testLength() {
    $content = new \PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertEquals(4, $content->length());
  }

  /**
  * @covers \PapayaResponseContentFile::output
  */
  public function testOutput() {
    $content = new \PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    ob_start();
    $content->output();
    $this->assertEquals('DATA', ob_get_clean());
  }

  /**
  * @covers \PapayaResponseContentFile::__toString
  */
  public function testMagicMethodToString() {
    $content = new \PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertEquals('DATA', (string)$content);
  }

}
