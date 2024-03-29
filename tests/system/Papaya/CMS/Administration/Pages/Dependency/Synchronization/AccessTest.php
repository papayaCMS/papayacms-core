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

namespace Papaya\CMS\Administration\Pages\Dependency\Synchronization;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class AccessTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Access::page
   */
  public function testTranslationsGetAfterSet() {
    $page = $this->createMock(\Papaya\CMS\Content\Page\Work::class);
    $action = new Access();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Access::page
   */
  public function testTranslationsGetImplicitCreate() {
    $action = new Access();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Work::class, $action->page()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Access::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Access::updatePages
   */
  public function testSynchronize() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $page = $this->getPageFixture(
      $databaseAccess,
      array(
        'inheritVisitorPermissions' => 1,
        'visitorPermissions' => '1;2',
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic',
        array(
          'topic_modified' => 84,
          'surfer_useparent' => 1,
          'surfer_permids' => '1;2'
        ),
        array(
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new Access();
    $action->page($page);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Access::synchronize
   */
  public function testSynchronizePageNotLoaded() {
    $page = $this->createMock(\Papaya\CMS\Content\Page\Work::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $action = new Access();
    $action->page($page);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   ********************************/

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Access
   */
  private function getDatabaseAccessFixture() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getTimestamp')
      ->will($this->returnValue(84));
    return $databaseAccess;
  }

  private function getPageFixture(\Papaya\Database\Access $databaseAccess, array $data = array()) {
    $page = $this->createMock(\Papaya\CMS\Content\Page\Work::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function ($name) use ($data) {
          return $data[$name];
        }
      );
    $page
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    return $page;
  }
}
