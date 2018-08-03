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

class PapayaUiImagesTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Images::__construct
  */
  public function testConstructorAddsImages() {
    $images = new \Papaya\UI\Images(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );

  }

  /**
  * @covers \Papaya\UI\Images::add
  */
  public function testAdd() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \Papaya\UI\Images::add
  */
  public function testAddIgnoreExisting() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'success.png'));
    $images->add(array('test' => 'fail.png'), \Papaya\UI\Images::DUPLICATES_IGNORE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \Papaya\UI\Images::add
  */
  public function testAddOverwriteExisting() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'fail.png'));
    $images->add(array('test' => 'success.png'), \Papaya\UI\Images::DUPLICATES_OVERWRITE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \Papaya\UI\Images::remove
  */
  public function testRemove() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'test.png'));
    $images->remove(array('test'));
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }

  /**
  * @covers \Papaya\UI\Images::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'test.png'));
    $this->assertTrue(isset($images['test']));
  }

  /**
  * @covers \Papaya\UI\Images::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $images = new \Papaya\UI\Images();
    $this->assertFalse(isset($images['test']));
  }

  /**
  * @covers \Papaya\UI\Images::offsetGet
  */
  public function testOffsetGet() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'test.png'));
    $this->assertEquals('test.png', $images['test']);
  }

  /**
  * @covers \Papaya\UI\Images::offsetGet
  */
  public function testOffsetGetWithNonExistingOffsetExpectingGivenOffset() {
    $images = new \Papaya\UI\Images();
    $this->assertSame('test', $images['test']);
  }

  /**
  * @covers \Papaya\UI\Images::offsetSet
  */
  public function testOffsetSet() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'fail.png'));
    $images['test'] = 'success.png';
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers \Papaya\UI\Images::offsetUnset
  */
  public function testOffsetUnset() {
    $images = new \Papaya\UI\Images();
    $images->add(array('test' => 'test.png'));
    unset($images['test']);
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }
}
