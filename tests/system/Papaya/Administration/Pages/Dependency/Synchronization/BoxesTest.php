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

use Papaya\Administration\Pages\Dependency\Synchronization\Boxes;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationBoxesTest extends PapayaTestCase {

  /**
  * @covers Boxes::synchronize
  * @covers Boxes::setInheritanceStatus
  */
  public function testSynchronize() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with('topic_id', array(42))
      ->will($this->returnValue("topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with("UPDATE %s SET box_useparent = '%d' WHERE topic_id = '42'", array('table_topic', 1))
      ->will($this->returnValue(TRUE));

    $page = $this->createMock(PapayaContentPageWork::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $page
      ->expects($this->once())
      ->method('__get')
      ->with('inheritBoxes')
      ->will($this->returnValue(1));

    $boxes = $this->createMock(PapayaContentPageBoxes::class);
    $boxes
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(TRUE));
    $boxes
      ->expects($this->once())
      ->method('copyTo')
      ->with(array(42))
      ->will($this->returnValue(TRUE));

    $action = new Boxes();
    $action->page($page);
    $action->boxes($boxes);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers Boxes::synchronize
  * @covers Boxes::setInheritanceStatus
  */
  public function testSynchronizeLoadFailed() {
    $page = $this->createMock(PapayaContentPageWork::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(FALSE));
    $action = new Boxes();
    $action->page($page);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers Boxes::boxes
  */
  public function testBoxesGetAfterSet() {
    $boxes = $this->createMock(PapayaContentPageBoxes::class);
    $action = new Boxes();
    $this->assertSame(
      $boxes, $action->boxes($boxes)
    );
  }

  /**
  * @covers Boxes::boxes
  */
  public function testBoxesGetImplicitCreate() {
    $action = new Boxes();
    $this->assertInstanceOf(
      PapayaContentPageBoxes::class, $action->boxes()
    );
  }

  /**
  * @covers Boxes::page
  */
  public function testPageGetAfterSet() {
    $page = $this->createMock(PapayaContentPageWork::class);
    $action = new Boxes();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers Boxes::page
  */
  public function testPageGetImplicitCreate() {
    $action = new Boxes();
    $this->assertInstanceOf(
      PapayaContentPageWork::class, $action->page()
    );
  }
}
