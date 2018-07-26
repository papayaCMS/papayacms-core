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

use Papaya\Administration\Pages\Dependency\Synchronizations;
use Papaya\Administration\Pages\Dependency\Synchronization;
use Papaya\Content\Page\Dependencies;
use Papaya\Content\Page\Dependency;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationsTest extends \PapayaTestCase {

  /**
  * @covers Synchronizations::getIcons
  */
  public function testGetIcons() {
    $synchronizations = new Synchronizations();
    $icons = $synchronizations->getIcons();
    $this->assertInstanceOf(\PapayaUiIconList::class, $icons);
    $this->assertSame($icons, $synchronizations->getIcons());
  }

  /**
  * @covers Synchronizations::getList
  */
  public function testGetList() {
    $synchronizations = new Synchronizations();
    $list = $synchronizations->getList();
    $this->assertInternalType('array', $list);
    $this->assertEquals($list, $synchronizations->getList());
  }

  /**
  * @covers Synchronizations::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $synchronizations = new Synchronizations();
    $dependencies = $this->createMock(Dependencies::class);
    $this->assertSame($dependencies, $synchronizations->dependencies($dependencies));
  }

  /**
  * @covers Synchronizations::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $synchronizations = new Synchronizations();
    $this->assertInstanceOf(Dependencies::class, $synchronizations->dependencies());
  }

  /**
  * @covers Synchronizations::getAction
  */
  public function testGetAction() {
    $synchronizations = new Synchronizations();
    $action = $synchronizations->getAction(Dependency::SYNC_PROPERTIES);
    $this->assertInstanceOf(
      Synchronization::class,
      $action
    );
  }

  /**
  * @covers Synchronizations::getAction
  */
  public function testGetActionExpectingNull() {
    $synchronizations = new Synchronizations();
    $this->assertNull(
      $synchronizations->getAction(-1)
    );
  }

  /**
  * @covers Synchronizations::getTargets
  */
  public function testGetTargets() {
    $dependencies = $this->createMock(Dependencies::class);
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
          new ArrayIterator(
            array(
              23 => array(
                'id' => 23,
                'synchronization' => Dependency::SYNC_PROPERTIES
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
      $synchronizations->getTargets(42, Dependency::SYNC_PROPERTIES)
    );
  }

  /**
  * @covers Synchronizations::getTargets
  */
  public function testGetTargetsExpectingNull() {
    $dependencies = $this->createMock(Dependencies::class);
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
          new ArrayIterator(
            array()
          )
        )
      );
    $synchronizations = new Synchronizations();
    $synchronizations->dependencies($dependencies);
    $this->assertNull(
      $synchronizations->getTargets(42, Dependency::SYNC_PROPERTIES)
    );
  }

  /**
  * @covers Synchronizations::synchronizeDependency
  */
  public function testSynchronizeDependency() {
    $dependency = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => Dependency::SYNC_PROPERTIES
      )
    );
    $action = $this->createMock(Synchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, NULL);
    $synchronizations =
      new \PapayaAdministrationPagesDependencySynchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->synchronizeDependency($dependency);
  }

  /**
  * @covers Synchronizations::synchronizeAction
  */
  public function testSynchronizeAction() {
    $action = $this->createMock(Synchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, array(3, 4));
    $synchronizations =
      new \PapayaAdministrationPagesDependencySynchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->targetsList = array(21);
    $synchronizations->synchronizeAction(
      Dependency::SYNC_PROPERTIES, 42, array(3, 4)
    );
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|Dependency
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(Dependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator($data))
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }
}

class PapayaAdministrationPagesDependencySynchronizations_TestProxy
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
