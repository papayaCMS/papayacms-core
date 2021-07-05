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

namespace Papaya\Administration\Pages\Dependency;

require_once __DIR__.'/../../../../../bootstrap.php';

class SynchronizationsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getIcons
   */
  public function testGetIcons() {
    $synchronizations = new Synchronizations();
    $icons = $synchronizations->getIcons();
    $this->assertInstanceOf(\Papaya\UI\Icon\Collection::class, $icons);
    $this->assertSame($icons, $synchronizations->getIcons());
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getList
   */
  public function testGetList() {
    $synchronizations = new Synchronizations();
    $list = $synchronizations->getList();
    $this->assertIsArray($list);
    $this->assertEquals($list, $synchronizations->getList());
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::dependencies
   */
  public function testDependenciesGetAfterSet() {
    $synchronizations = new Synchronizations();
    $dependencies = $this->createMock(\Papaya\Content\Page\Dependencies::class);
    $this->assertSame($dependencies, $synchronizations->dependencies($dependencies));
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::dependencies
   */
  public function testDependenciesGetImplicitCreate() {
    $synchronizations = new Synchronizations();
    $this->assertInstanceOf(\Papaya\Content\Page\Dependencies::class, $synchronizations->dependencies());
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getAction
   */
  public function testGetAction() {
    $synchronizations = new Synchronizations();
    $action = $synchronizations->getAction(\Papaya\Content\Page\Dependency::SYNC_PROPERTIES);
    $this->assertInstanceOf(
      Synchronization::class,
      $action
    );
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getAction
   */
  public function testGetActionExpectingNull() {
    $synchronizations = new Synchronizations();
    $this->assertNull(
      $synchronizations->getAction(-1)
    );
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getTargets
   */
  public function testGetTargets() {
    $dependencies = $this->createMock(\Papaya\Content\Page\Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $dependencies
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              23 => array(
                'id' => 23,
                'synchronization' => \Papaya\Content\Page\Dependency::SYNC_PROPERTIES
              ),
              46 => array(
                'id' => 46,
                'synchronization' => 0
              )
            )
          )
        )
      );
    $synchronizations = new Synchronizations();
    $synchronizations->dependencies($dependencies);
    $this->assertEquals(
      array(23),
      $synchronizations->getTargets(42, \Papaya\Content\Page\Dependency::SYNC_PROPERTIES)
    );
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::getTargets
   */
  public function testGetTargetsExpectingNull() {
    $dependencies = $this->createMock(\Papaya\Content\Page\Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(FALSE));
    $dependencies
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array()
          )
        )
      );
    $synchronizations = new Synchronizations();
    $synchronizations->dependencies($dependencies);
    $this->assertNull(
      $synchronizations->getTargets(42, \Papaya\Content\Page\Dependency::SYNC_PROPERTIES)
    );
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::synchronizeDependency
   */
  public function testSynchronizeDependency() {
    $dependency = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => \Papaya\Content\Page\Dependency::SYNC_PROPERTIES
      )
    );
    $action = $this->createMock(Synchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, NULL);
    $synchronizations =
      new Synchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->synchronizeDependency($dependency);
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Synchronizations::synchronizeAction
   */
  public function testSynchronizeAction() {
    $action = $this->createMock(Synchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, array(3, 4));
    $synchronizations =
      new Synchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->targetsList = array(21);
    $synchronizations->synchronizeAction(
      \Papaya\Content\Page\Dependency::SYNC_PROPERTIES, 42, array(3, 4)
    );
  }

  /**************************
   * Fixtures
   **************************/

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Page\Dependency
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(\Papaya\Content\Page\Dependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new \ArrayIterator($data))
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }
}

class Synchronizations_TestProxy
  extends Synchronizations {

  public $actionMock;
  public $targetsList;

  public function getAction($synchronization) {
    return $this->actionMock;
  }

  public function getTargets($originId, $synchronization) {
    return $this->targetsList;
  }
}
