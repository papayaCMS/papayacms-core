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

use Papaya\Content\Module\Options;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginOptionStorageTest extends PapayaTestCase {

  /**
  * @covers \PapayaPluginOptionStorage::__construct
  */
  public function testConstructor() {
    $storage = new \PapayaPluginOptionStorage('AB123456789012345678901234567890');
    $this->assertAttributeEquals(
      'ab123456789012345678901234567890', '_guid', $storage
    );
  }

  /**
  * @covers \PapayaPluginOptionStorage::load
  */
  public function testLoad() {
    $options = $this->createMock(Options::class);
    $options
      ->expects($this->once())
      ->method('load')
      ->with(array('guid' => 'ab123456789012345678901234567890'))
      ->will($this->returnValue(TRUE));
    $storage = new \PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers \PapayaPluginOptionStorage::getIterator
  */
  public function testGetIterator() {
    $options = $this->createMock(Options::class);
    $options
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              array(
                'name' => 'foo',
                'value' => 'bar'
              )
            )
          )
        )
      );
    $storage = new \PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($storage)
    );
  }

  /**
  * @covers \PapayaPluginOptionStorage::options
  */
  public function testOptionsGetAfterSet() {
    $options = $this->createMock(Options::class);
    $storage = new \PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertSame($options, $storage->options());
  }

  /**
  * @covers \PapayaPluginOptionStorage::options
  */
  public function testOptionsGetImplicitCreate() {
    $storage = new \PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $this->assertInstanceOf(Options::class, $options = $storage->options());
  }
}
