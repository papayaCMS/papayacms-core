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

use Papaya\Configuration\Storage;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginOptionsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Plugin\Options::__construct
  */
  public function testConstructor() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $this->assertAttributeEquals(
      'ab123456789012345678901234567890', '_guid', $options
    );
  }

  /**
  * @covers \Papaya\Plugin\Options::load
  * @covers \Papaya\Plugin\Options::getStatus
  * @covers \Papaya\Plugin\Options::getIterator
  */
  public function testLoad() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->load($this->getStorageFixture(array('SAMPLE_OPTION' => '42'), TRUE));
    $this->assertEquals(
      \Papaya\Plugin\Options::STATUS_LOADED, $options->getStatus()
    );
    $this->assertEquals(
      array(
        'SAMPLE_OPTION' => '42'
      ),
      iterator_to_array($options)
    );
  }

  /**
  * @covers \Papaya\Plugin\Options::get
  * @covers \Papaya\Plugin\Options::lazyLoad
  */
  public function testGet() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertEquals(
      '42', $options['SAMPLE_OPTION']
    );
  }

  /**
  * @covers \Papaya\Plugin\Options::get
  * @covers \Papaya\Plugin\Options::set
  * @covers \Papaya\Plugin\Options::lazyLoad
  */
  public function testGetAfterSet() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $options['SAMPLE_OPTION'] = '21';
    $this->assertEquals(
      '21', $options['SAMPLE_OPTION']
    );
  }

  /**
  * @covers \Papaya\Plugin\Options::has
  * @covers \Papaya\Plugin\Options::lazyLoad
  */
  public function testHasExpectingTrue() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertTrue($options->has('SAMPLE_OPTION'));
  }

  /**
  * @covers \Papaya\Plugin\Options::has
  * @covers \Papaya\Plugin\Options::lazyLoad
  */
  public function testHasExpectingFalse() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertFalse($options->has('INVALID_OPTION'));
  }

  /**
  * @covers \Papaya\Plugin\Options::storage
  */
  public function testStorageGetAfterSet() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $options->storage($storage = $this->getStorageFixture());
    $this->assertSame($storage, $options->storage());
  }

  /**
  * @covers \Papaya\Plugin\Options::storage
  */
  public function testStorageImplicitCreate() {
    $options = new \Papaya\Plugin\Options('ab123456789012345678901234567890');
    $this->assertInstanceOf(Storage::class, $options->storage());
  }

  /**
   * @param array $data
   * @param bool $requireLoading
   * @return PHPUnit_Framework_MockObject_MockObject|Storage
   */
  public function getStorageFixture(array $data = array(), $requireLoading = FALSE) {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($requireLoading ? $this->once() : $this->any())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($data)));
    return $storage;
  }
}
