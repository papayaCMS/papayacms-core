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

namespace Papaya\Application {

  require_once __DIR__.'/../../../bootstrap.php';

  class BaseObjectTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Application\BaseObject::setApplication
     */
    public function testSetGetApplication() {
      $object = new BaseObject_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Application $application */
      $application = $this->createMock(\Papaya\Application::class);
      /** @noinspection PhpDeprecationInspection */
      $object->setApplication($application);
      $this->assertSame(
        $application,
        $object->getApplication()
      );
    }

    /**
     * @covers \Papaya\Application\BaseObject::getApplication
     */
    public function testGetApplicationSingleton() {
      $object = new BaseObject_TestProxy();
      /** @noinspection PhpDeprecationInspection */
      $application = $object->getApplication();
      $this->assertInstanceOf(
        \Papaya\Application::class,
        $application
      );
      $this->assertSame(
        $application,
        $object->getApplication()
      );
    }

    /**
     * @covers \Papaya\Application\BaseObject::papaya
     */
    public function testPapayaGetAfterSet() {
      $object = new BaseObject_TestProxy();
      $application = $this->createMock(\Papaya\Application::class);
      $this->assertSame($application, $object->papaya($application));
    }

    /**
     * @covers \Papaya\Application\BaseObject::papaya
     */
    public function testPapayaGetUsingSingleton() {
      $object = new BaseObject_TestProxy();
      $application = $object->papaya();
      $this->assertInstanceOf(\Papaya\Application::class, $object->papaya());
      $this->assertSame($application, $object->papaya());
    }
  }

  class BaseObject_TestProxy extends BaseObject {
  }
}
