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

namespace Papaya\UI {

  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Images
   */
  class ImagesTest extends TestCase {

    public function testConstructorAddsImages() {
      $images = new Images(['test' => 'test.png']);
      $this->assertEquals(
        ['test' => 'test.png'],
        iterator_to_array($images)
      );
    }

    public function testAdd() {
      $images = new Images();
      $images->add(['test' => 'test.png']);
      $this->assertEquals(
        ['test' => 'test.png'],
        iterator_to_array($images)
      );
    }

    public function testAddIgnoreExisting() {
      $images = new Images();
      $images->add(['test' => 'success.png']);
      $images->add(['test' => 'fail.png'], Images::DUPLICATES_IGNORE);
      $this->assertEquals(
        ['test' => 'success.png'],
        iterator_to_array($images)
      );
    }

    public function testAddOverwriteExisting() {
      $images = new Images();
      $images->add(['test' => 'fail.png']);
      $images->add(['test' => 'success.png'], Images::DUPLICATES_OVERWRITE);
      $this->assertEquals(
        ['test' => 'success.png'],
        iterator_to_array($images)
      );
    }

    public function testRemove() {
      $images = new Images();
      $images->add(['test' => 'test.png']);
      $images->remove(['test']);
      $this->assertEquals(
        [],
        iterator_to_array($images)
      );
    }

    public function testOffsetExistsExpectingTrue() {
      $images = new Images();
      $images->add(['test' => 'test.png']);
      $this->assertTrue(isset($images['test']));
    }

    public function testOffsetExistsExpectingFalse() {
      $images = new Images();
      $this->assertFalse(isset($images['test']));
    }

    public function testOffsetGet() {
      $images = new Images();
      $images->add(['test' => 'test.png']);
      $this->assertEquals('test.png', $images['test']);
    }

    public function testOffsetGetWithNonExistingOffsetExpectingGivenOffset() {
      $images = new Images();
      $this->assertSame('test', $images['test']);
    }

    public function testOffsetSet() {
      $images = new Images();
      $images->add(['test' => 'fail.png']);
      $images['test'] = 'success.png';
      $this->assertEquals(
        ['test' => 'success.png'],
        iterator_to_array($images)
      );
    }

    public function testOffsetUnset() {
      $images = new Images();
      $images->add(['test' => 'test.png']);
      unset($images['test']);
      $this->assertEquals(
        [],
        iterator_to_array($images)
      );
    }
  }
}
