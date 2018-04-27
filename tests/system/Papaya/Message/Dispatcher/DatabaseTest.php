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

require_once __DIR__.'/../../../../bootstrap.php';
PapayaTestCase::defineConstantDefaults(
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

class PapayaMessageDispatcherDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchExpectingTrue() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('table_log'), $this->isNull(), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_INFO));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->createMock(PapayaMessageContextGroup::class)));
    $dispatcher = new PapayaMessageDispatcherDatabase();
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
  * @covers PapayaMessageDispatcherDatabase::dispatch
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchWithDebugMessageExpectingTrue() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('table_log'), $this->isNull(), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $user = $this
      ->getMockBuilder(base_auth::class)
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_DEBUG));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->createMock(PapayaMessageContextGroup::class)));
    $dispatcher = new PapayaMessageDispatcherDatabase();
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
  * @covers PapayaMessageDispatcherDatabase::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  */
  public function testDispatchWithDebugMessageExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_DEBUG));
    $dispatcher = new PapayaMessageDispatcherDatabase();
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
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchPreventMessageRecursionDefault() {
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $this->assertAttributeEquals(
      FALSE,
      '_preventMessageRecursion',
      $dispatcher
    );
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchWithRecursion() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('insertRecord');
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_INFO));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->createMock(PapayaMessageContextGroup::class)));
    $dispatcher = new PapayaMessageDispatcherDatabaseProxy();
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
    $this->assertAttributeEquals(
      TRUE,
      '_preventMessageRecursion',
      $dispatcher
    );
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::allow
  * @dataProvider allowDataProvider
  *
  * @param boolean $expected
  * @param integer $type
  * @param boolean $dispatcherActive
  * @param boolean $dispatcherHandleDebug
  */
  public function testAllow($expected, $type, $dispatcherActive, $dispatcherHandleDebug) {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($type));
    $dispatcher = new PapayaMessageDispatcherDatabase();
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
      array(FALSE, PapayaMessage::SEVERITY_INFO, FALSE, FALSE),
      array(TRUE, PapayaMessage::SEVERITY_INFO, TRUE, FALSE),
      array(FALSE, PapayaMessage::SEVERITY_DEBUG, TRUE, FALSE),
      array(TRUE, PapayaMessage::SEVERITY_INFO, TRUE, TRUE),
      array(TRUE, PapayaMessage::SEVERITY_DEBUG, TRUE, TRUE),
      array(FALSE, PapayaMessage::SEVERITY_DEBUG, TRUE, FALSE)
    );
  }
}

class PapayaMessageDispatcherDatabaseProxy
  extends PapayaMessageDispatcherDatabase {
  /* change the default to what we want to test */
  protected $_preventMessageRecursion = TRUE;
}
