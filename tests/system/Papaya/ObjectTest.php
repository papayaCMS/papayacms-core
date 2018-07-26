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

require_once __DIR__.'/../../bootstrap.php';

class PapayaObjectTest extends PapayaTestCase {

  /**
  * @covers \PapayaObject::setApplication
  */
  public function testSetApplication() {
    $object = new \PapayaObject_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaApplication $application */
    $application = $this->createMock(PapayaApplication::class);
    /** @noinspection PhpDeprecationInspection */
    $object->setApplication($application);
    $this->assertAttributeSame(
      $application, '_applicationObject', $object
    );
  }

  /**
  * @covers \PapayaObject::getApplication
  */
  public function testGetApplication() {
    $object = new \PapayaObject_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaApplication $application */
    $application = $this->createMock(PapayaApplication::class);
    /** @noinspection PhpDeprecationInspection */
    $object->setApplication($application);
    /** @noinspection PhpDeprecationInspection */
    $this->assertSame(
      $application,
      $object->getApplication()
    );
  }

  /**
  * @covers \PapayaObject::getApplication
  */
  public function testGetApplicationSingleton() {
    $object = new \PapayaObject_TestProxy();
    /** @noinspection PhpDeprecationInspection */
    $app = $object->getApplication();
    $this->assertInstanceOf(
      \PapayaApplication::class,
      $app
    );
    $this->assertAttributeSame(
      $app, '_applicationObject', $object
    );
  }

  /**
  * @covers \PapayaObject::papaya
  */
  public function testPapayaGetAfterSet() {
    $object = new \PapayaObject_TestProxy();
    $application = $this->createMock(PapayaApplication::class);
    $this->assertSame($application, $object->papaya($application));
  }

  /**
  * @covers \PapayaObject::papaya
  */
  public function testPapayaGetUsingSingleton() {
    $object = new \PapayaObject_TestProxy();
    $application = $object->papaya();
    $this->assertInstanceOf(PapayaApplication::class, $object->papaya());
    $this->assertSame($application, $object->papaya());
  }
}

class PapayaObject_TestProxy extends PapayaObject{
}
