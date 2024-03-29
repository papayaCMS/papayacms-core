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

namespace Papaya\CMS\Plugin\Option;

require_once __DIR__.'/../../../../../bootstrap.php';

class StorageTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Plugin\Option\Storage::__construct
   */
  public function testConstructor() {
    $storage = new Storage('AB123456789012345678901234567890');
    $this->assertEquals(
      'ab123456789012345678901234567890', $storage->getGUID()
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Storage::load
   */
  public function testLoad() {
    $options = $this->createMock(\Papaya\CMS\Content\Module\Options::class);
    $options
      ->expects($this->once())
      ->method('load')
      ->with(array('guid' => 'ab123456789012345678901234567890'))
      ->willReturn(TRUE);
    $storage = new Storage('ab123456789012345678901234567890');
    $storage->options($options);
    $storage->load();
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Storage::getIterator
   */
  public function testGetIterator() {
    $options = $this->createMock(\Papaya\CMS\Content\Module\Options::class);
    $options
      ->expects($this->once())
      ->method('getIterator')
      ->willReturn(
        new \ArrayIterator(
          [
            [
              'name' => 'foo',
              'value' => 'bar'
            ]
          ]
        )
      );
    $storage = new Storage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($storage)
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Storage::options
   */
  public function testOptionsGetAfterSet() {
    $options = $this->createMock(\Papaya\CMS\Content\Module\Options::class);
    $storage = new Storage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertSame($options, $storage->options());
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Storage::options
   */
  public function testOptionsGetImplicitCreate() {
    $storage = new Storage('ab123456789012345678901234567890');
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Module\Options::class, $options = $storage->options());
  }
}
