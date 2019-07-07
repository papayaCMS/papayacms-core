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

namespace Papaya\Theme;
require_once __DIR__.'/../../../bootstrap.php';

class CollectionTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Theme\Collection::getIterator
   */
  public function testGetIterator() {
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue(__DIR__.'/TestDataList/'));
    $list = new Collection();
    $list->handler($handler);
    $this->assertEquals(
      array(
        'theme-sample'
      ),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\Theme\Collection::getDefinition
   */
  public function testGetDefinition() {
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme-sample')
      ->will($this->returnValue(new \Papaya\Content\Structure()));
    $list = new Collection();
    $list->handler($handler);
    $this->assertInstanceOf(
      \Papaya\Content\Structure::class,
      $list->getDefinition('theme-sample')
    );
  }

  /**
   * @covers \Papaya\Theme\Collection::handler
   */
  public function testHandlerGetAfterSet() {
    $list = new Collection();
    $list->handler($handler = $this->createMock(Handler::class));
    $this->assertSame($handler, $list->handler());
  }

  /**
   * @covers \Papaya\Theme\Collection::handler
   */
  public function testHandlerGetImplicitCreate() {
    $list = new Collection();
    $this->assertInstanceOf(Handler::class, $list->handler());
  }
}

