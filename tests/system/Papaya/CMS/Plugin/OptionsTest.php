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

namespace Papaya\CMS\Plugin;
require_once __DIR__.'/../../../../bootstrap.php';

class OptionsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Plugin\Options::__construct
   */
  public function testConstructor() {
    $options = new Options('ab123456789012345678901234567890');
    $this->assertEquals(
      'ab123456789012345678901234567890', $options->getGUID()
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::load
   * @covers \Papaya\CMS\Plugin\Options::getStatus
   * @covers \Papaya\CMS\Plugin\Options::getIterator
   */
  public function testLoad() {
    $options = new Options('ab123456789012345678901234567890');
    $options->load($this->getStorageFixture(array('SAMPLE_OPTION' => '42'), TRUE));
    $this->assertEquals(
      Options::STATUS_LOADED, $options->getStatus()
    );
    $this->assertEquals(
      array(
        'SAMPLE_OPTION' => '42'
      ),
      iterator_to_array($options)
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::get
   * @covers \Papaya\CMS\Plugin\Options::lazyLoad
   */
  public function testGet() {
    $options = new Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertEquals(
      '42', $options['SAMPLE_OPTION']
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::get
   * @covers \Papaya\CMS\Plugin\Options::set
   * @covers \Papaya\CMS\Plugin\Options::lazyLoad
   */
  public function testGetAfterSet() {
    $options = new Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $options['SAMPLE_OPTION'] = '21';
    $this->assertEquals(
      '21', $options['SAMPLE_OPTION']
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::has
   * @covers \Papaya\CMS\Plugin\Options::lazyLoad
   */
  public function testHasExpectingTrue() {
    $options = new Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertTrue($options->has('SAMPLE_OPTION'));
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::has
   * @covers \Papaya\CMS\Plugin\Options::lazyLoad
   */
  public function testHasExpectingFalse() {
    $options = new Options('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertFalse($options->has('INVALID_OPTION'));
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::storage
   */
  public function testStorageGetAfterSet() {
    $options = new Options('ab123456789012345678901234567890');
    $options->storage($storage = $this->getStorageFixture());
    $this->assertSame($storage, $options->storage());
  }

  /**
   * @covers \Papaya\CMS\Plugin\Options::storage
   */
  public function testStorageImplicitCreate() {
    $options = new Options('ab123456789012345678901234567890');
    $this->assertInstanceOf(\Papaya\Configuration\Storage::class, $options->storage());
  }

  /**
   * @param array $data
   * @param bool $requireLoading
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Configuration\Storage
   */
  public function getStorageFixture(array $data = array(), $requireLoading = FALSE) {
    $storage = $this->createMock(\Papaya\Configuration\Storage::class);
    $storage
      ->expects($requireLoading ? $this->once() : $this->any())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator($data)));
    return $storage;
  }
}
