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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiImagesTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiImages::__construct
  */
  public function testConstructorAddsImages() {
    $images = new \PapayaUiImages(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );

  }

  /**
  * @covers \PapayaUiImages::add
  */
  public function testAdd() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \PapayaUiImages::add
  */
  public function testAddIgnoreExisting() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'success.png'));
    $images->add(array('test' => 'fail.png'), PapayaUiImages::DUPLICATES_IGNORE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \PapayaUiImages::add
  */
  public function testAddOverwriteExisting() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'fail.png'));
    $images->add(array('test' => 'success.png'), PapayaUiImages::DUPLICATES_OVERWRITE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \PapayaUiImages::remove
  */
  public function testRemove() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $images->remove(array('test'));
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }

  /**
  * @covers \PapayaUiImages::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertTrue(isset($images['test']));
  }

  /**
  * @covers \PapayaUiImages::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $images = new \PapayaUiImages();
    $this->assertFalse(isset($images['test']));
  }

  /**
  * @covers \PapayaUiImages::offsetGet
  */
  public function testOffsetGet() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertEquals('test.png', $images['test']);
  }

  /**
  * @covers \PapayaUiImages::offsetGet
  */
  public function testOffsetGetWithNonExistingOffsetExpectingGivenOffset() {
    $images = new \PapayaUiImages();
    $this->assertSame('test', $images['test']);
  }

  /**
  * @covers \PapayaUiImages::offsetSet
  */
  public function testOffsetSet() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'fail.png'));
    $images['test'] = 'success.png';
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \PapayaUiImages::offsetUnset
  */
  public function testOffsetUnset() {
    $images = new \PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    unset($images['test']);
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }
}
