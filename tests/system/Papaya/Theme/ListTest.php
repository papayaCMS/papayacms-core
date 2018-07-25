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

use Papaya\Content\Structure;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaThemeListTest extends PapayaTestCase {

  /**
   * @covers PapayaThemeList::getIterator
   * @covers PapayaThemeList::callbackGetName
   */
  public function testGetIterator() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue(__DIR__.'/TestDataList/'));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertEquals(
      array(
        'theme-sample'
      ),
      iterator_to_array($list)
    );
  }

  /**
   * @covers PapayaThemeList::getDefinition
   */
  public function testGetDefinition() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $handler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme-sample')
      ->will($this->returnValue(new Structure()));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertInstanceOf(
      Structure::class,
      $list->getDefinition('theme-sample')
    );
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetAfterSet() {
    $list = new PapayaThemeList();
    $list->handler($handler =  $this->createMock(PapayaThemeHandler::class));
    $this->assertSame($handler, $list->handler());
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetImplicitCreate() {
    $list = new PapayaThemeList();
    $this->assertInstanceOf(PapayaThemeHandler::class, $list->handler());
  }
}

