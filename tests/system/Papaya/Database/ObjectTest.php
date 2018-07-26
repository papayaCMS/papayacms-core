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

use Papaya\Database\BaseObject;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseObjectTest extends PapayaTestCase {

  /**
  * @covers BaseObject::setDatabaseAccess
  */
  public function testSetDatabaseAccess() {
    $databaseObject = new BaseObject();
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertAttributeSame(
      $databaseAccess,
      '_databaseAccessObject',
      $databaseObject
    );
  }

  /**
  * @covers BaseObject::getDatabaseAccess
  */
  public function testGetDatabaseAccess() {
    $databaseObject = new BaseObject();
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess,
      $databaseObject->getDatabaseAccess()
    );
  }

  /**
  * @covers BaseObject::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplicitCreate() {
    $application = $this->mockPapaya()->application();
    $databaseObject = new BaseObject();
    $databaseObject->papaya($application);
    $databaseAccess = $databaseObject->getDatabaseAccess();
    $this->assertInstanceOf(
      \Papaya\Database\Access::class, $databaseAccess
    );
    $this->assertSame(
      $application,
      $databaseAccess->papaya()
    );
  }

  /**
  * @covers BaseObject::__call
  */
  public function testDelegation() {
    $databaseObject = new BaseObject();
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->equalTo('SQL'), $this->equalTo(array('SAMPLE')))
      ->will($this->returnValue(TRUE));
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $databaseObject->databaseQueryFmt('SQL', array('SAMPLE'))
    );
  }

  /**
  * @covers BaseObject::__call
  */
  public function testDelegationWithInvalidFunction() {
    $databaseObject = new BaseObject();
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $databaseObject->invalidFunctionName();
  }
}

