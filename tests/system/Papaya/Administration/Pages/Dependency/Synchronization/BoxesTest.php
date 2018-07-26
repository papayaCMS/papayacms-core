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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationBoxesTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::synchronize
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::setInheritanceStatus
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

    $page = $this->createMock(Papaya\Content\Page\Work::class);
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

    $boxes = $this->createMock(Papaya\Content\Page\Boxes::class);
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

    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $action->page($page);
    $action->boxes($boxes);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::synchronize
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::setInheritanceStatus
  */
  public function testSynchronizeLoadFailed() {
    $page = $this->createMock(Papaya\Content\Page\Work::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(FALSE));
    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $action->page($page);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::boxes
  */
  public function testBoxesGetAfterSet() {
    $boxes = $this->createMock(Papaya\Content\Page\Boxes::class);
    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $this->assertSame(
      $boxes, $action->boxes($boxes)
    );
  }

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::boxes
  */
  public function testBoxesGetImplicitCreate() {
    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $this->assertInstanceOf(
      Papaya\Content\Page\Boxes::class, $action->boxes()
    );
  }

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::page
  */
  public function testPageGetAfterSet() {
    $page = $this->createMock(Papaya\Content\Page\Work::class);
    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers \Papaya\Administration\Pages\Dependency\Synchronization\Boxes::page
  */
  public function testPageGetImplicitCreate() {
    $action = new \Papaya\Administration\Pages\Dependency\Synchronization\Boxes();
    $this->assertInstanceOf(
      Papaya\Content\Page\Work::class, $action->page()
    );
  }
}
