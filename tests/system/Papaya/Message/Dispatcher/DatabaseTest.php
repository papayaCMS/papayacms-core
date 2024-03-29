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

namespace Papaya\Message\Dispatcher {

  require_once __DIR__.'/../../../../bootstrap.php';
  \Papaya\TestFramework\TestCase::defineConstantDefaults(
    array(
      'PAPAYA_DB_TBL_AUTHOPTIONS',
      'PAPAYA_DB_TBL_AUTHUSER',
      'PAPAYA_DB_TBL_AUTHGROUPS',
      'PAPAYA_DB_TBL_AUTHLINK',
      'PAPAYA_DB_TBL_AUTHPERM',
      'PAPAYA_DB_TBL_AUTHMODPERMS',
      'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
      'PAPAYA_DB_TBL_SURFER'
    )
  );

  class DatabaseTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Message\Dispatcher\Database::dispatch
     * @covers \Papaya\Message\Dispatcher\Database::save
     */
    public function testDispatchExpectingTrue() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with($this->equalTo('table_log'), $this->isNull(), $this->isType('array'))
        ->will($this->returnValue(TRUE));
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message\Logable::class);
      $message
        ->expects($this->once())
        ->method('getGroup')
        ->will($this->returnValue(\Papaya\Message\Logable::GROUP_SYSTEM));
      $message
        ->expects($this->atLeastOnce())
        ->method('getSeverity')
        ->will($this->returnValue(\Papaya\Message::SEVERITY_INFO));
      $message
        ->expects($this->atLeastOnce())
        ->method('getMessage')
        ->will($this->returnValue('Sample message'));
      $message
        ->expects($this->atLeastOnce())
        ->method('context')
        ->will($this->returnValue($this->createMock(\Papaya\Message\Context\Group::class)));
      $dispatcher = new Database();
      $dispatcher->papaya(
        $this->mockPapaya()->application(
          array(
            'Options' => $this->mockPapaya()->options(
              array(
                'PAPAYA_PROTOCOL_DATABASE' => TRUE
              )
            )
          )
        )
      );
      $dispatcher->setDatabaseAccess($databaseAccess);
      $this->assertTrue($dispatcher->dispatch($message));
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::dispatch
     * @covers \Papaya\Message\Dispatcher\Database::save
     */
    public function testDispatchWithDebugMessageExpectingTrue() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with($this->equalTo('table_log'), $this->isNull(), $this->isType('array'))
        ->will($this->returnValue(TRUE));
      $user = $this
        ->getMockBuilder(\base_auth::class)
        ->setMethods(array('isLoggedIn', 'getUserId', 'getDisplayName'))
        ->getMock();
      $user
        ->expects($this->once())
        ->method('isLoggedIn')
        ->will($this->returnValue(TRUE));
      $user
        ->expects($this->once())
        ->method('getUserId')
        ->will($this->returnValue('123'));
      $user
        ->expects($this->once())
        ->method('getDisplayName')
        ->will($this->returnValue('Sample User'));
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message\Logable::class);
      $message
        ->expects($this->once())
        ->method('getGroup')
        ->will($this->returnValue(\Papaya\Message\Logable::GROUP_SYSTEM));
      $message
        ->expects($this->atLeastOnce())
        ->method('getSeverity')
        ->will($this->returnValue(\Papaya\Message::SEVERITY_DEBUG));
      $message
        ->expects($this->atLeastOnce())
        ->method('getMessage')
        ->will($this->returnValue('Sample message'));
      $message
        ->expects($this->atLeastOnce())
        ->method('context')
        ->will($this->returnValue($this->createMock(\Papaya\Message\Context\Group::class)));
      $dispatcher = new Database();
      $dispatcher->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $user,
            'Options' => $this->mockPapaya()->options(
              array(
                'PAPAYA_PROTOCOL_DATABASE' => TRUE,
                'PAPAYA_PROTOCOL_DATABASE_DEBUG' => TRUE
              )
            )
          )
        )
      );
      $dispatcher->setDatabaseAccess($databaseAccess);
      $this->assertTrue($dispatcher->dispatch($message));
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::dispatch
     */
    public function testDispatchWithInvalidMessageExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message::class);
      $dispatcher = new Database();
      $this->assertFalse($dispatcher->dispatch($message));
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::dispatch
     */
    public function testDispatchWithDebugMessageExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message\Logable::class);
      $message
        ->expects($this->once())
        ->method('getSeverity')
        ->will($this->returnValue(\Papaya\Message::SEVERITY_DEBUG));
      $dispatcher = new Database();
      $dispatcher->papaya(
        $this->mockPapaya()->application(
          array(
            'Options' => $this->mockPapaya()->options(
              array(
                'PAPAYA_PROTOCOL_DATABASE' => TRUE,
                'PAPAYA_PROTOCOL_DATABASE_DEBUG' => FALSE
              )
            )
          )
        )
      );
      $this->assertFalse($dispatcher->dispatch($message));
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::save
     */
    public function testDispatchPreventMessageRecursionDefault() {
      $dispatcher = new Database();
      $this->assertEquals(
        FALSE,
        $dispatcher->preventMessageRecursion()
      );
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::save
     */
    public function testDispatchWithRecursion() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('insertRecord');
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message\Logable::class);
      $message
        ->expects($this->once())
        ->method('getGroup')
        ->will($this->returnValue(\Papaya\Message\Logable::GROUP_SYSTEM));
      $message
        ->expects($this->atLeastOnce())
        ->method('getSeverity')
        ->will($this->returnValue(\Papaya\Message::SEVERITY_INFO));
      $message
        ->expects($this->atLeastOnce())
        ->method('getMessage')
        ->will($this->returnValue('Sample message'));
      $message
        ->expects($this->atLeastOnce())
        ->method('context')
        ->will($this->returnValue($this->createMock(\Papaya\Message\Context\Group::class)));
      $dispatcher = new Dispatcher_DatabaseProxy();
      $dispatcher->papaya(
        $this->mockPapaya()->application(
          array(
            'Options' => $this->mockPapaya()->options(
              array(
                'PAPAYA_PROTOCOL_DATABASE' => TRUE
              )
            )
          )
        )
      );
      $dispatcher->setDatabaseAccess($databaseAccess);
      $this->assertFalse($dispatcher->dispatch($message));
      $this->assertEquals(
        TRUE,
        $dispatcher->preventMessageRecursion()
      );
    }

    /**
     * @covers \Papaya\Message\Dispatcher\Database::allow
     * @dataProvider allowDataProvider
     *
     * @param boolean $expected
     * @param integer $type
     * @param boolean $dispatcherActive
     * @param boolean $dispatcherHandleDebug
     */
    public function testAllow($expected, $type, $dispatcherActive, $dispatcherHandleDebug) {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
      $message = $this->createMock(\Papaya\Message\Logable::class);
      $message
        ->expects($this->any())
        ->method('getSeverity')
        ->will($this->returnValue($type));
      $dispatcher = new Database();
      $dispatcher->papaya(
        $this->mockPapaya()->application(
          array(
            'Options' => $this->mockPapaya()->options(
              array(
                'PAPAYA_PROTOCOL_DATABASE' => $dispatcherActive,
                'PAPAYA_PROTOCOL_DATABASE_DEBUG' => $dispatcherHandleDebug
              )
            )
          )
        )
      );
      $this->assertSame(
        $expected,
        $dispatcher->allow($message)
      );
    }

    public static function allowDataProvider() {
      return array(
        array(FALSE, \Papaya\Message::SEVERITY_INFO, FALSE, FALSE),
        array(TRUE, \Papaya\Message::SEVERITY_INFO, TRUE, FALSE),
        array(FALSE, \Papaya\Message::SEVERITY_DEBUG, TRUE, FALSE),
        array(TRUE, \Papaya\Message::SEVERITY_INFO, TRUE, TRUE),
        array(TRUE, \Papaya\Message::SEVERITY_DEBUG, TRUE, TRUE),
        array(FALSE, \Papaya\Message::SEVERITY_DEBUG, TRUE, FALSE)
      );
    }
  }

  class Dispatcher_DatabaseProxy
    extends Database {
    /* change the default to what we want to test */
    protected $_preventMessageRecursion = TRUE;
  }
}
