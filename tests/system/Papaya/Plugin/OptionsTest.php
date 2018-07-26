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
  * @covers \PapayaPluginOptions::__construct
  */
  public function testConstructor() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $this->assertAttributeEquals(
      'ab123456789012345678901234567890', '_guid', $options
    );
  }

  /**
  * @covers \PapayaPluginOptions::load
  * @covers \PapayaPluginOptions::getStatus
  * @covers \PapayaPluginOptions::getIterator
  */
  public function testLoad() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->load($this->getStorageFixture(array('SAMPLE_OPTION' => '42'), TRUE));
    $this->assertEquals(
      \PapayaPluginOptions::STATUS_LOADED, $options->getStatus()
    );
    $this->assertEquals(
      array(
        'SAMPLE_OPTION' => '42'
      ),
      iterator_to_array($options)
    );
  }

  /**
  * @covers \PapayaPluginOptions::get
  * @covers \PapayaPluginOptions::lazyLoad
  */
  public function testGet() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertEquals(
      '42', $options['SAMPLE_OPTION']
    );
  }

  /**
  * @covers \PapayaPluginOptions::get
  * @covers \PapayaPluginOptions::set
  * @covers \PapayaPluginOptions::lazyLoad
  */
  public function testGetAfterSet() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $options['SAMPLE_OPTION'] = '21';
    $this->assertEquals(
      '21', $options['SAMPLE_OPTION']
    );
  }

  /**
  * @covers \PapayaPluginOptions::has
  * @covers \PapayaPluginOptions::lazyLoad
  */
  public function testHasExpectingTrue() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertTrue($options->has('SAMPLE_OPTION'));
  }

  /**
  * @covers \PapayaPluginOptions::has
  * @covers \PapayaPluginOptions::lazyLoad
  */
  public function testHasExpectingFalse() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->storage($this->getStorageFixture(array('SAMPLE_OPTION' => '42')));
    $this->assertFalse($options->has('INVALID_OPTION'));
  }

  /**
  * @covers \PapayaPluginOptions::storage
  */
  public function testStorageGetAfterSet() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
    $options->storage($storage = $this->getStorageFixture());
    $this->assertSame($storage, $options->storage());
  }

  /**
  * @covers \PapayaPluginOptions::storage
  */
  public function testStorageImplicitCreate() {
    $options = new \PapayaPluginOptions('ab123456789012345678901234567890');
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
